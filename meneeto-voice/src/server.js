const express = require('express');
const path = require('path');
const crypto = require('crypto');
const session = require('express-session');
const { readJson, writeJson } = require('./jsonStore');
const { generateAudioWithGemini } = require('./gemini');

const FILES = {
  accessKeys: 'access-keys.json',
  voiceModels: 'voice-models.json',
  settings: 'settings.json',
  stats: 'stats.json'
};

const ADMIN_PASSWORD = 'lolilol787898';
const HUMAN_CHECK_ANSWER = '8';

function nowIso() {
  return new Date().toISOString();
}

function createServer() {
  const app = express();

  app.use(express.json({ limit: '1mb' }));
  app.use(express.urlencoded({ extended: true }));
  app.use(
    session({
      secret: process.env.SESSION_SECRET || 'meneeto-voice-secret',
      resave: false,
      saveUninitialized: false,
      cookie: {
        httpOnly: true,
        sameSite: 'lax',
        secure: false
      }
    })
  );

  app.use((req, res, next) => {
    if (req.path.startsWith('/api/admin')) return next();
    const stats = readJson(FILES.stats, { visits: 0, generations: 0, keyChecks: 0, updatedAt: nowIso() });
    stats.visits += 1;
    stats.updatedAt = nowIso();
    writeJson(FILES.stats, stats);
    next();
  });

  app.use('/assets', express.static(path.join(__dirname, '..', 'public')));

  app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, '..', 'public', 'index.html'));
  });

  app.get('/admin', (req, res) => {
    res.sendFile(path.join(__dirname, '..', 'public', 'admin.html'));
  });

  app.get('/api/voice-models', (req, res) => {
    const models = readJson(FILES.voiceModels, []);
    res.json(models.filter((m) => m.active !== false).map((m) => ({ id: m.id, label: m.label })));
  });

  app.post('/api/key-info', (req, res) => {
    const { accessKey } = req.body;
    if (!accessKey) {
      return res.status(400).json({ error: 'Access key is required.' });
    }

    const keys = readJson(FILES.accessKeys, []);
    const keyEntry = keys.find((k) => k.key === accessKey);

    const stats = readJson(FILES.stats, { visits: 0, generations: 0, keyChecks: 0, updatedAt: nowIso() });
    stats.keyChecks += 1;
    stats.updatedAt = nowIso();
    writeJson(FILES.stats, stats);

    if (!keyEntry) {
      return res.status(404).json({ error: 'Key not found.' });
    }

    return res.json({
      key: keyEntry.key,
      usesRemaining: keyEntry.usesRemaining,
      expiresAt: keyEntry.expiresAt,
      isExpired: new Date(keyEntry.expiresAt) < new Date()
    });
  });

  app.post('/api/generate', async (req, res) => {
    const { accessKey, text, voiceModelId } = req.body;

    if (!accessKey || !text || !voiceModelId) {
      return res.status(400).json({ error: 'accessKey, text and voiceModelId are required.' });
    }

    if (text.length > 500) {
      return res.status(400).json({ error: 'Text cannot exceed 500 characters.' });
    }

    const keys = readJson(FILES.accessKeys, []);
    const keyIdx = keys.findIndex((k) => k.key === accessKey);
    if (keyIdx === -1) {
      return res.status(403).json({ error: 'Invalid access key.' });
    }

    const keyEntry = keys[keyIdx];
    if (new Date(keyEntry.expiresAt) < new Date()) {
      return res.status(403).json({ error: 'Access key has expired.' });
    }

    if (keyEntry.usesRemaining <= 0) {
      return res.status(403).json({ error: 'No remaining tokens on this key.' });
    }

    const models = readJson(FILES.voiceModels, []);
    const modelEntry = models.find((m) => m.id === voiceModelId && m.active !== false);
    if (!modelEntry) {
      return res.status(404).json({ error: 'Voice model not found.' });
    }

    const settings = readJson(FILES.settings, {
      geminiApiKey: '',
      geminiModel: 'gemini-2.5-flash-preview-tts'
    });

    try {
      const audio = await generateAudioWithGemini({
        apiKey: settings.geminiApiKey,
        model: settings.geminiModel,
        text,
        stylePrompt: modelEntry.prompt
      });

      keyEntry.usesRemaining -= 1;
      keyEntry.updatedAt = nowIso();
      keys[keyIdx] = keyEntry;
      writeJson(FILES.accessKeys, keys);

      const stats = readJson(FILES.stats, { visits: 0, generations: 0, keyChecks: 0, updatedAt: nowIso() });
      stats.generations += 1;
      stats.updatedAt = nowIso();
      writeJson(FILES.stats, stats);

      return res.json({
        mimeType: audio.mimeType,
        audioBase64: audio.base64Audio,
        usesRemaining: keyEntry.usesRemaining
      });
    } catch (error) {
      return res.status(500).json({ error: error.message || 'Audio generation failed.' });
    }
  });

  function ensureAdmin(req, res, next) {
    if (!req.session?.adminAuth) {
      return res.status(401).json({ error: 'Unauthorized' });
    }
    next();
  }

  app.post('/api/admin/login', (req, res) => {
    const { password, humanCheck } = req.body;
    if (password !== ADMIN_PASSWORD) {
      return res.status(401).json({ error: 'Wrong password.' });
    }
    if (String(humanCheck).trim() !== HUMAN_CHECK_ANSWER) {
      return res.status(401).json({ error: 'Human verification failed.' });
    }

    req.session.adminAuth = true;
    res.json({ ok: true });
  });

  app.post('/api/admin/logout', ensureAdmin, (req, res) => {
    req.session.destroy(() => res.json({ ok: true }));
  });

  app.get('/api/admin/bootstrap', ensureAdmin, (req, res) => {
    const stats = readJson(FILES.stats, { visits: 0, generations: 0, keyChecks: 0, updatedAt: nowIso() });
    const settings = readJson(FILES.settings, { geminiApiKey: '', geminiModel: 'gemini-2.5-flash-preview-tts' });
    const models = readJson(FILES.voiceModels, []);
    const keys = readJson(FILES.accessKeys, []);

    res.json({ stats, settings: { geminiApiKey: settings.geminiApiKey ? '********' : '', geminiModel: settings.geminiModel }, models, keys });
  });

  app.put('/api/admin/settings', ensureAdmin, (req, res) => {
    const { geminiApiKey, geminiModel } = req.body;
    const current = readJson(FILES.settings, { geminiApiKey: '', geminiModel: 'gemini-2.5-flash-preview-tts' });

    const nextSettings = {
      geminiApiKey: typeof geminiApiKey === 'string' && geminiApiKey.trim() ? geminiApiKey.trim() : current.geminiApiKey,
      geminiModel: typeof geminiModel === 'string' && geminiModel.trim() ? geminiModel.trim() : current.geminiModel,
      updatedAt: nowIso()
    };

    writeJson(FILES.settings, nextSettings);
    res.json({ ok: true });
  });

  app.post('/api/admin/models', ensureAdmin, (req, res) => {
    const { label, prompt } = req.body;
    if (!label || !prompt) {
      return res.status(400).json({ error: 'label and prompt are required' });
    }
    const models = readJson(FILES.voiceModels, []);
    const model = {
      id: crypto.randomUUID(),
      label: label.trim(),
      prompt: prompt.trim(),
      active: true,
      createdAt: nowIso(),
      updatedAt: nowIso()
    };
    models.push(model);
    writeJson(FILES.voiceModels, models);
    res.json(model);
  });

  app.put('/api/admin/models/:id', ensureAdmin, (req, res) => {
    const models = readJson(FILES.voiceModels, []);
    const idx = models.findIndex((m) => m.id === req.params.id);
    if (idx === -1) return res.status(404).json({ error: 'Model not found' });

    models[idx] = {
      ...models[idx],
      label: req.body.label?.trim() || models[idx].label,
      prompt: req.body.prompt?.trim() || models[idx].prompt,
      active: typeof req.body.active === 'boolean' ? req.body.active : models[idx].active,
      updatedAt: nowIso()
    };
    writeJson(FILES.voiceModels, models);
    res.json(models[idx]);
  });

  app.delete('/api/admin/models/:id', ensureAdmin, (req, res) => {
    const models = readJson(FILES.voiceModels, []);
    const filtered = models.filter((m) => m.id !== req.params.id);
    writeJson(FILES.voiceModels, filtered);
    res.json({ ok: true });
  });

  app.post('/api/admin/keys', ensureAdmin, (req, res) => {
    const { usesRemaining, expiresAt } = req.body;
    const keys = readJson(FILES.accessKeys, []);

    const key = crypto.randomBytes(8).toString('hex');
    const entry = {
      id: crypto.randomUUID(),
      key,
      usesRemaining: Number(usesRemaining) || 10,
      expiresAt: expiresAt || new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toISOString(),
      createdAt: nowIso(),
      updatedAt: nowIso()
    };
    keys.push(entry);
    writeJson(FILES.accessKeys, keys);
    res.json(entry);
  });

  app.put('/api/admin/keys/:id', ensureAdmin, (req, res) => {
    const keys = readJson(FILES.accessKeys, []);
    const idx = keys.findIndex((k) => k.id === req.params.id);
    if (idx === -1) return res.status(404).json({ error: 'Key not found' });

    keys[idx] = {
      ...keys[idx],
      usesRemaining: Number.isFinite(Number(req.body.usesRemaining)) ? Number(req.body.usesRemaining) : keys[idx].usesRemaining,
      expiresAt: req.body.expiresAt || keys[idx].expiresAt,
      updatedAt: nowIso()
    };

    writeJson(FILES.accessKeys, keys);
    res.json(keys[idx]);
  });

  app.delete('/api/admin/keys/:id', ensureAdmin, (req, res) => {
    const keys = readJson(FILES.accessKeys, []);
    const filtered = keys.filter((k) => k.id !== req.params.id);
    writeJson(FILES.accessKeys, filtered);
    res.json({ ok: true });
  });

  return app;
}

module.exports = createServer;
