const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");

const root = path.join(__dirname, "..");
const html = fs.readFileSync(path.join(root, "index.html"), "utf8");
const css = fs.readFileSync(path.join(root, "styles.css"), "utf8");

test("landing page highlights the requested product areas", () => {
  assert.match(html, /CMS/i);
  assert.match(html, /Email Marketing/i);
  assert.match(html, /Small HRM/i);
  assert.match(html, /kanggui-rcm\.com/i);
});

test("landing page ships with the shared stylesheet", () => {
  assert.match(html, /styles\.css/);
  assert.match(css, /\.hero/);
  assert.match(css, /\.cards/);
});
