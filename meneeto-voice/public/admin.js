const loginSection = document.getElementById('adminLogin');
const panel = document.getElementById('adminPanel');

async function api(url, options = {}) {
  const res = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...options
  });
  const data = await res.json();
  return { res, data };
}

async function bootstrap() {
  const { res, data } = await api('/api/admin/bootstrap');
  if (!res.ok) return;

  loginSection.classList.add('hidden');
  panel.classList.remove('hidden');

  document.getElementById('stats').textContent = `Visites: ${data.stats.visits} • Audios: ${data.stats.generations} • Vérifications de clés: ${data.stats.keyChecks}`;
  document.getElementById('geminiModel').value = data.settings.geminiModel || '';
  renderModels(data.models);
  renderKeys(data.keys);
}

function renderModels(models) {
  const list = document.getElementById('modelsList');
  list.innerHTML = models.map((m) => `
    <div class="item">
      <strong>${m.label}</strong>
      <p>${m.prompt}</p>
      <button class="btn ghost" onclick="deleteModel('${m.id}')">Supprimer</button>
    </div>`).join('');
}

function renderKeys(keys) {
  const list = document.getElementById('keysList');
  list.innerHTML = keys.map((k) => `
    <div class="item">
      <strong>${k.key}</strong>
      <p>Jetons: ${k.usesRemaining} | Expire: ${new Date(k.expiresAt).toLocaleString()}</p>
      <input id="uses-${k.id}" type="number" value="${k.usesRemaining}" />
      <input id="exp-${k.id}" type="datetime-local" value="${new Date(k.expiresAt).toISOString().slice(0,16)}" />
      <button class="btn secondary" onclick="updateKey('${k.id}')">Mettre à jour</button>
      <button class="btn ghost" onclick="deleteKey('${k.id}')">Supprimer</button>
    </div>`).join('');
}

document.getElementById('loginBtn').addEventListener('click', async () => {
  const password = document.getElementById('adminPassword').value;
  const humanCheck = document.getElementById('humanCheck').value;
  const { res, data } = await api('/api/admin/login', {
    method: 'POST',
    body: JSON.stringify({ password, humanCheck })
  });

  if (!res.ok) {
    document.getElementById('loginMsg').textContent = data.error || 'Erreur de connexion';
    return;
  }

  bootstrap();
});

document.getElementById('saveSettings').addEventListener('click', async () => {
  const geminiApiKey = document.getElementById('geminiApiKey').value;
  const geminiModel = document.getElementById('geminiModel').value;
  await api('/api/admin/settings', {
    method: 'PUT',
    body: JSON.stringify({ geminiApiKey, geminiModel })
  });
  alert('Configuration enregistrée');
});

document.getElementById('addModel').addEventListener('click', async () => {
  const label = document.getElementById('modelLabel').value;
  const prompt = document.getElementById('modelPrompt').value;
  await api('/api/admin/models', {
    method: 'POST',
    body: JSON.stringify({ label, prompt })
  });
  bootstrap();
});

document.getElementById('addKey').addEventListener('click', async () => {
  const usesRemaining = Number(document.getElementById('keyUses').value || 10);
  const expiresAtInput = document.getElementById('keyExpiry').value;
  const expiresAt = expiresAtInput ? new Date(expiresAtInput).toISOString() : undefined;

  await api('/api/admin/keys', {
    method: 'POST',
    body: JSON.stringify({ usesRemaining, expiresAt })
  });
  bootstrap();
});

window.deleteModel = async (id) => {
  await api(`/api/admin/models/${id}`, { method: 'DELETE' });
  bootstrap();
};

window.updateKey = async (id) => {
  const usesRemaining = Number(document.getElementById(`uses-${id}`).value);
  const expiresAt = new Date(document.getElementById(`exp-${id}`).value).toISOString();
  await api(`/api/admin/keys/${id}`, {
    method: 'PUT',
    body: JSON.stringify({ usesRemaining, expiresAt })
  });
  bootstrap();
};

window.deleteKey = async (id) => {
  await api(`/api/admin/keys/${id}`, { method: 'DELETE' });
  bootstrap();
};

bootstrap();
