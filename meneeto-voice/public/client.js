const textArea = document.getElementById('text');
const count = document.getElementById('count');
const form = document.getElementById('generateForm');
const modelSelect = document.getElementById('voiceModel');
const accessKeyInput = document.getElementById('accessKey');
const resultEl = document.getElementById('result');
const audioPlayer = document.getElementById('audioPlayer');
const remainingTokens = document.getElementById('remainingTokens');

const pricingModal = document.getElementById('pricingModal');
const checkModal = document.getElementById('checkModal');

document.getElementById('buyBtn').addEventListener('click', () => pricingModal.classList.remove('hidden'));
document.getElementById('checkBtn').addEventListener('click', () => checkModal.classList.remove('hidden'));
document.getElementById('buyFromCheck').addEventListener('click', () => {
  pricingModal.classList.remove('hidden');
  checkModal.classList.add('hidden');
});

document.querySelectorAll('[data-close]').forEach((btn) => {
  btn.addEventListener('click', () => document.getElementById(btn.dataset.close).classList.add('hidden'));
});

textArea.addEventListener('input', () => {
  count.textContent = String(textArea.value.length);
});

async function loadModels() {
  const res = await fetch('/api/voice-models');
  const models = await res.json();
  modelSelect.innerHTML = '';
  models.forEach((m) => {
    const opt = document.createElement('option');
    opt.value = m.id;
    opt.textContent = m.label;
    modelSelect.appendChild(opt);
  });
}

function prefillKeyFromUrl() {
  const params = new URLSearchParams(window.location.search);
  const key = params.get('key');
  if (key) {
    accessKeyInput.value = key;
    document.getElementById('checkKeyInput').value = key;
  }
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (textArea.value.length > 500) {
    alert('Le texte dépasse 500 caractères.');
    return;
  }

  const res = await fetch('/api/generate', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      accessKey: accessKeyInput.value.trim(),
      text: textArea.value,
      voiceModelId: modelSelect.value
    })
  });

  const data = await res.json();
  if (!res.ok) {
    alert(data.error || 'Erreur');
    return;
  }

  audioPlayer.src = `data:${data.mimeType};base64,${data.audioBase64}`;
  remainingTokens.textContent = `Jetons restants: ${data.usesRemaining}`;
  resultEl.classList.remove('hidden');
});

document.getElementById('checkKeyBtn').addEventListener('click', async () => {
  const k = document.getElementById('checkKeyInput').value.trim();
  const info = document.getElementById('keyInfo');
  const buyFromCheck = document.getElementById('buyFromCheck');

  const res = await fetch('/api/key-info', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ accessKey: k })
  });
  const data = await res.json();

  if (!res.ok) {
    info.textContent = data.error || 'Clé invalide';
    buyFromCheck.classList.remove('hidden');
    return;
  }

  info.textContent = `Jetons restants: ${data.usesRemaining}. Expiration: ${new Date(data.expiresAt).toLocaleString()}`;
  buyFromCheck.classList.remove('hidden');
});

loadModels();
prefillKeyFromUrl();
