const fs = require('fs');
const path = require('path');

const dataDir = path.join(__dirname, '..', 'data');

function getPath(fileName) {
  return path.join(dataDir, fileName);
}

function readJson(fileName, fallback) {
  const filePath = getPath(fileName);
  if (!fs.existsSync(filePath)) {
    writeJson(fileName, fallback);
    return fallback;
  }

  const raw = fs.readFileSync(filePath, 'utf-8');
  return JSON.parse(raw);
}

function writeJson(fileName, data) {
  const filePath = getPath(fileName);
  fs.writeFileSync(filePath, JSON.stringify(data, null, 2));
}

module.exports = {
  readJson,
  writeJson
};
