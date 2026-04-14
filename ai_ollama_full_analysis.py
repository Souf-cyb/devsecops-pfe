import json
import glob
import requests
from datetime import datetime

OLLAMA_URL = "http://localhost:11434/api/generate"
MODEL = "llama3"


def ask_ollama(prompt):
    response = requests.post(
        OLLAMA_URL,
        json={
            "model": MODEL,
            "prompt": prompt,
            "stream": False
        }
    )
    return response.json()["response"]


def load_reports():
    data = {}

    tools = ["semgrep", "zap"]

    for tool in tools:
        files = glob.glob(f"reports/{tool}/*.json")
        tool_data = []

        for f in files:
            try:
                with open(f, "r", encoding="utf-8") as file:
                    tool_data.append(json.load(file))
            except:
                continue

        data[tool] = tool_data

    return data


def build_prompt(data):

    prompt = f"""
You are a cybersecurity expert.

Analyze these security reports:

SEMgrep:
{json.dumps(data.get("semgrep", []))[:4000]}

ZAP:
{json.dumps(data.get("zap", []))[:4000]}

Tasks:
1. Summarize vulnerabilities
2. Detect false positives
3. Give remediation steps
4. Provide severity classification

Be concise and structured.
"""

    return prompt


def main():
    print("[+] Loading reports...")
    data = load_reports()

    print("[+] Sending to Ollama...")

    prompt = build_prompt(data)
    result = ask_ollama(prompt)

    filename = f"ai_report_{datetime.now().strftime('%H%M%S')}.txt"

    with open(filename, "w", encoding="utf-8") as f:
        f.write(result)

    print(f"[+] Done: {filename}")


if __name__ == "__main__":
    main()
