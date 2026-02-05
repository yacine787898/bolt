async function generateAudioWithGemini({ apiKey, model, text, stylePrompt }) {
  if (!apiKey || !model) {
    throw new Error('Gemini API key or model is missing in admin settings.');
  }

  const endpoint = `https://generativelanguage.googleapis.com/v1beta/models/${encodeURIComponent(model)}:generateContent?key=${encodeURIComponent(apiKey)}`;

  const payload = {
    contents: [
      {
        parts: [
          {
            text: `You are a text to speech generator. Voice style instructions: ${stylePrompt}. Speak this exact text in that style:\n\n${text}`
          }
        ]
      }
    ],
    generationConfig: {
      responseModalities: ['AUDIO']
    }
  };

  const res = await fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  if (!res.ok) {
    const errBody = await res.text();
    throw new Error(`Gemini API error (${res.status}): ${errBody}`);
  }

  const data = await res.json();
  const part = data?.candidates?.[0]?.content?.parts?.find((p) => p.inlineData?.data);

  if (!part?.inlineData?.data) {
    throw new Error('No audio data returned by Gemini API.');
  }

  return {
    mimeType: part.inlineData.mimeType || 'audio/wav',
    base64Audio: part.inlineData.data
  };
}

module.exports = {
  generateAudioWithGemini
};
