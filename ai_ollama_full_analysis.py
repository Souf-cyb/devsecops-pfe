import json
import os
import re
import requests
from datetime import datetime
from html import escape

# =========================
# CONFIG
# =========================
OLLAMA_URL = os.getenv("OLLAMA_URL", "http://localhost:11434/api/generate")
OLLAMA_MODEL = os.getenv("OLLAMA_MODEL", "llama3")
ZAP_JSON_PATH = os.getenv("ZAP_JSON_PATH", "reports/zap.json")

# =========================
# OLLAMA CALL
# =========================
def ask_ollama(prompt):
    response = requests.post(
        OLLAMA_URL,
        json={
            "model": OLLAMA_MODEL,
            "prompt": prompt,
            "stream": False,
            "options": {
                "temperature": 0.2
            }
        },
        timeout=600
    )
    response.raise_for_status()
    return response.json().get("response", "")

# =========================
# JSON HELPERS
# =========================
def safe_load_json(path):
    try:
        with open(path, "r", encoding="utf-8", errors="ignore") as f:
            return json.load(f)
    except Exception as e:
        print(f"[ERROR] Impossible de charger {path}: {e}")
        return None

def extract_json_object(text):
    if not text:
        return None

    txt = text.strip()
    txt = re.sub(r"^```json\s*", "", txt, flags=re.IGNORECASE)
    txt = re.sub(r"^```\s*", "", txt)
    txt = re.sub(r"\s*```$", "", txt)

    start = txt.find("{")
    end = txt.rfind("}")

    if start != -1 and end != -1 and end > start:
        candidate = txt[start:end + 1]
        try:
            return json.loads(candidate)
        except Exception:
            return None

    return None

# =========================
# ZAP PARSING
# =========================
def normalize_severity_from_riskcode(riskcode):
    riskcode = str(riskcode).strip()
    if riskcode == "3":
        return "HIGH"
    if riskcode == "2":
        return "MEDIUM"
    if riskcode == "1":
        return "LOW"
    return "INFO"

def load_zap_findings():
    data = safe_load_json(ZAP_JSON_PATH)
    if not data:
        return []

    findings = []

    # Structure ZAP possible :
    # {
    #   "site": [
    #      {
    #        "name": "...",
    #        "alerts": [...]
    #      }
    #   ]
    # }
    sites = []
    if isinstance(data, dict):
        s = data.get("site", [])
        if isinstance(s, dict):
            sites = [s]
        elif isinstance(s, list):
            sites = s
    elif isinstance(data, list):
        sites = data

    for site in sites:
        if not isinstance(site, dict):
            continue

        alerts = site.get("alerts", [])
        if not isinstance(alerts, list):
            continue

        for alert in alerts:
            if not isinstance(alert, dict):
                continue

            instances = alert.get("instances", [])
            uri = ""
            if isinstance(instances, list) and instances:
                first = instances[0]
                if isinstance(first, dict):
                    uri = first.get("uri", "")

            riskcode = alert.get("riskcode", "0")
            severity = normalize_severity_from_riskcode(riskcode)

            findings.append({
                "tool": "ZAP",
                "type": "DAST",
                "id": alert.get("pluginid", alert.get("name", "")),
                "title": alert.get("name", "ZAP Alert"),
                "severity": severity,
                "riskcode": riskcode,
                "riskdesc": alert.get("riskdesc", ""),
                "confidence": alert.get("confidence", ""),
                "uri": uri,
                "description": alert.get("desc", ""),
                "solution": alert.get("solution", ""),
                "cweid": alert.get("cweid", ""),
                "message": f"{alert.get('name', '')} - {alert.get('riskdesc', '')}",
                "report_context": (
                    f"Alert: {alert.get('name', '')}\n"
                    f"Risk: {alert.get('riskdesc', '')}\n"
                    f"Confidence: {alert.get('confidence', '')}\n"
                    f"CWE: {alert.get('cweid', '')}\n"
                    f"URI: {uri}\n"
                    f"Description: {alert.get('desc', '')}\n"
                    f"Suggested solution: {alert.get('solution', '')}"
                )
            })

    return findings

# =========================
# AI ANALYSIS
# =========================
def build_prompt(finding):
    return f"""
You are a senior DevSecOps security expert.

Application context:
- Flask e-commerce application
- Docker deployment
- Public web application

Tool:
- {finding['tool']}
Type:
- {finding['type']}

Finding:
- Title: {finding['title']}
- Severity: {finding['severity']}
- Risk: {finding['riskdesc']}
- Confidence: {finding['confidence']}
- CWE: {finding['cweid']}
- URI: {finding['uri']}

Report context:
{finding['report_context']}

Tasks:
1. Give a real priority score from 1 to 10.
2. Decide if this is a false positive or not.
3. Explain why.
4. Give remediation steps.
5. If possible, give an example of secure fix or mitigation.
6. Keep the answer practical and concise.

Return ONLY valid JSON with exactly these keys:
{{
  "priority_score": 1,
  "is_false_positive": false,
  "false_positive_reason": "",
  "remediation_summary": "",
  "remediation_code": "",
  "remediation_explanation": "",
  "evidence": ""
}}
""".strip()

def analyze_finding(finding):
    prompt = build_prompt(finding)

    try:
        raw = ask_ollama(prompt)
    except Exception as e:
        return {
            "priority_score": 5,
            "is_false_positive": False,
            "false_positive_reason": "",
            "remediation_summary": "AI call failed",
            "remediation_code": "",
            "remediation_explanation": str(e),
            "evidence": "",
            "raw_response": ""
        }

    parsed = extract_json_object(raw)
    if not parsed:
        return {
            "priority_score": 5,
            "is_false_positive": False,
            "false_positive_reason": "",
            "remediation_summary": "Manual review required",
            "remediation_code": "",
            "remediation_explanation": raw,
            "evidence": "",
            "raw_response": raw
        }

    return {
        "priority_score": int(parsed.get("priority_score", 5)),
        "is_false_positive": bool(parsed.get("is_false_positive", False)),
        "false_positive_reason": parsed.get("false_positive_reason", ""),
        "remediation_summary": parsed.get("remediation_summary", ""),
        "remediation_code": parsed.get("remediation_code", ""),
        "remediation_explanation": parsed.get("remediation_explanation", ""),
        "evidence": parsed.get("evidence", ""),
        "raw_response": raw
    }

# =========================
# HTML DASHBOARD
# =========================
def priority_class(score):
    if score >= 9:
        return "critical"
    if score >= 7:
        return "high"
    if score >= 4:
        return "medium"
    return "low"

def render_card(item):
    ai = item["ai"]
    score = ai["priority_score"]
    fp = ai["is_false_positive"]

    card_class = "fp-card" if fp else "real-card"
    badge_class = priority_class(score)
    fp_label = "FALSE POSITIVE" if fp else "REAL ISSUE"

    return f"""
    <div class="card {card_class}">
        <div class="card-top">
            <div>
                <h3>{escape(item['title'])}</h3>
                <div class="meta">
                    <span><b>Severity:</b> {escape(item['severity'])}</span>
                    <span><b>URI:</b> {escape(item['uri'] or 'N/A')}</span>
                    <span><b>CWE:</b> {escape(str(item['cweid']))}</span>
                </div>
            </div>
            <div class="badges">
                <span class="badge {badge_class}">PRIORITY {score}/10</span>
                <span class="badge {'fp' if fp else 'real'}">{fp_label}</span>
            </div>
        </div>

        <div class="section">
            <div class="label">Report context</div>
            <pre>{escape(item['report_context'])}</pre>
        </div>

        <div class="section">
            <div class="label">AI analysis</div>
            <p><b>Remediation summary:</b> {escape(ai.get('remediation_summary', ''))}</p>
            <p><b>Evidence:</b> {escape(ai.get('evidence', ''))}</p>
            <p><b>False positive reason:</b> {escape(ai.get('false_positive_reason', ''))}</p>
            <p><b>Remediation explanation:</b> {escape(ai.get('remediation_explanation', ''))}</p>
        </div>

        <div class="section nested">
            <div class="label">Suggested code / mitigation</div>
            <pre>{escape(ai.get('remediation_code', '') or 'N/A')}</pre>
        </div>

        <details class="raw">
            <summary>Show raw AI response</summary>
            <pre>{escape(ai.get('raw_response', ''))}</pre>
        </details>
    </div>
    """

def generate_html(analyzed):
    total = len(analyzed)
    fp_count = sum(1 for x in analyzed if x["ai"]["is_false_positive"])
    real_count = total - fp_count
    high_priority = sum(1 for x in analyzed if x["ai"]["priority_score"] >= 7)

    analyzed_sorted = sorted(
        analyzed,
        key=lambda x: (-x["ai"]["priority_score"], x["ai"]["is_false_positive"])
    )

    cards = "\n".join(render_card(item) for item in analyzed_sorted)

    html = f"""
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI ZAP Security Dashboard</title>
<style>
    * {{ box-sizing: border-box; }}
    body {{
        margin: 0;
        font-family: Arial, sans-serif;
        background: #0f172a;
        color: #e2e8f0;
        padding: 24px;
    }}
    .container {{ max-width: 1400px; margin: 0 auto; }}
    .header {{
        background: linear-gradient(135deg, #1d4ed8, #7c3aed);
        padding: 28px;
        border-radius: 16px;
        margin-bottom: 24px;
    }}
    .header h1 {{ margin: 0; font-size: 30px; }}
    .header p {{ margin: 8px 0 0; opacity: 0.9; }}
    .stats {{
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }}
    .stat {{
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 12px;
        padding: 18px;
        text-align: center;
    }}
    .stat .n {{ font-size: 32px; font-weight: 700; }}
    .stat .l {{ font-size: 12px; color: #94a3b8; text-transform: uppercase; }}
    .card {{
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 14px;
        margin-bottom: 18px;
        overflow: hidden;
        padding: 18px;
    }}
    .card.real-card {{ border-left: 5px solid #ef4444; }}
    .card.fp-card {{ border-left: 5px solid #64748b; opacity: 0.95; }}
    .card-top {{
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 14px;
    }}
    .card h3 {{ margin: 0 0 8px; font-size: 18px; }}
    .meta {{
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
        color: #94a3b8;
        font-size: 12px;
    }}
    .badges {{ display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }}
    .badge {{
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }}
    .badge.critical {{ background: #ef4444; color: white; }}
    .badge.high {{ background: #f97316; color: white; }}
    .badge.medium {{ background: #eab308; color: #111827; }}
    .badge.low {{ background: #64748b; color: white; }}
    .badge.fp {{ background: #94a3b8; color: #0f172a; }}
    .badge.real {{ background: #22c55e; color: #052e16; }}
    .section {{
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 12px;
        padding: 14px;
        margin-top: 12px;
    }}
    .section.nested {{ margin-top: 12px; }}
    .label {{
        font-size: 12px;
        color: #60a5fa;
        margin-bottom: 8px;
        text-transform: uppercase;
        font-weight: 700;
    }}
    pre {{
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: Consolas, monospace;
        font-size: 12px;
        color: #e2e8f0;
        background: transparent;
    }}
    details.raw {{
        margin-top: 12px;
        background: #0f172a;
        border: 1px dashed #334155;
        border-radius: 12px;
        padding: 12px;
    }}
    details.raw summary {{
        cursor: pointer;
        color: #93c5fd;
        font-weight: 700;
    }}
    footer {{
        text-align: center;
        color: #64748b;
        font-size: 12px;
        padding: 18px 0 8px;
    }}
    @media (max-width: 1100px) {{
        .stats {{ grid-template-columns: repeat(2, 1fr); }}
        .card-top {{ flex-direction: column; }}
        .badges {{ justify-content: flex-start; }}
    }}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🛡️ ZAP AI Security Dashboard</h1>
        <p>Model: {escape(OLLAMA_MODEL)} | Generated: {escape(str(datetime.now()))}</p>
    </div>

    <div class="stats">
        <div class="stat"><div class="n" style="color:#60a5fa">{total}</div><div class="l">Total findings</div></div>
        <div class="stat"><div class="n" style="color:#ef4444">{real_count}</div><div class="l">Real issues</div></div>
        <div class="stat"><div class="n" style="color:#94a3b8">{fp_count}</div><div class="l">False positives</div></div>
        <div class="stat"><div class="n" style="color:#f97316">{high_priority}</div><div class="l">High priority</div></div>
    </div>

    {cards if cards else "<p>No ZAP findings found.</p>"}

    <footer>ShopVuln DevSecOps PFE • ZAP local analysis with Ollama</footer>
</div>
</body>
</html>
"""
    return html

# =========================
# MAIN
# =========================
def main():
    print("[+] Loading ZAP report...")

    if not os.path.exists(ZAP_JSON_PATH):
        print(f"[ERROR] File not found: {ZAP_JSON_PATH}")
        return

    findings = load_zap_findings()
    print(f"[+] ZAP findings loaded: {len(findings)}")

    analyzed = []

    for i, finding in enumerate(findings, start=1):
        print(f"[AI] {i}/{len(findings)} analyzing: {finding['title']}")

        ai_result = analyze_finding(finding)
        analyzed.append({
            **finding,
            "ai": ai_result
        })

    # Save JSON result
    with open("ai_zap_results.json", "w", encoding="utf-8") as f:
        json.dump(
            {
                "generated_at": str(datetime.now()),
                "model": OLLAMA_MODEL,
                "total_findings": len(analyzed),
                "results": analyzed
            },
            f,
            indent=2,
            ensure_ascii=False
        )

    # Save HTML dashboard
    html = generate_html(analyzed)
    with open("ai_zap_dashboard.html", "w", encoding="utf-8") as f:
        f.write(html)

    print("[+] Done:")
    print("    - ai_zap_results.json")
    print("    - ai_zap_dashboard.html")

if __name__ == "__main__":
    main()