/**
 * PHP-WASM Application Server
 *
 * Runs the PHP application via WebAssembly PHP 8.3 with SQLite.
 */

const express = require('express');
const session = require('express-session');
const path = require('path');
const fs = require('fs');
const { PHP } = require('@php-wasm/universal');
const { loadNodeRuntime, useHostFilesystem } = require('@php-wasm/node');

const app = express();
const PORT = process.env.PORT || 3000;
const PROJECT_ROOT = __dirname;

// Parse request bodies
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Sessions
app.use(session({
  secret: 'kangguircm-session-secret',
  resave: false,
  saveUninitialized: false,
  cookie: { httpOnly: true, maxAge: 120 * 60 * 1000, sameSite: 'lax' },
}));

// Serve static files directly
app.use('/uploads', express.static(path.join(PROJECT_ROOT, 'public/uploads')));
app.use('/css', express.static(path.join(PROJECT_ROOT, 'public/css')));
app.use('/js', express.static(path.join(PROJECT_ROOT, 'public/js')));

let phpInstance = null;

async function initPHP() {
  if (phpInstance) return phpInstance;
  console.log('⏳ Loading PHP 8.3 WASM runtime...');
  phpInstance = new PHP(await loadNodeRuntime('8.3', {
    emscriptenOptions: { processId: 1 },
  }));
  useHostFilesystem(phpInstance);
  console.log('✅ PHP 8.3 runtime loaded');
  return phpInstance;
}

function buildPhpRequest(req) {
  const uri = req.originalUrl || '/';
  const method = req.method;
  const sessionData = {};
  if (req.session) {
    for (const [k, v] of Object.entries(req.session)) {
      if (k === 'cookie') continue;
      sessionData[k] = v;
    }
  }

  return `<?php
// Setup superglobals
\$_SERVER['REQUEST_METHOD'] = '${method}';
\$_SERVER['REQUEST_URI'] = '${uri.replace(/'/g, "\\'")}';
\$_SERVER['PATH_INFO'] = '${uri.split('?')[0].replace(/'/g, "\\'")}';
\$_SERVER['SCRIPT_NAME'] = '/index.php';
\$_SERVER['SCRIPT_FILENAME'] = '${path.join(PROJECT_ROOT, 'public', 'index.php')}';
\$_SERVER['DOCUMENT_ROOT'] = '${path.join(PROJECT_ROOT, 'public')}';
\$_SERVER['SERVER_NAME'] = 'localhost';
\$_SERVER['SERVER_PORT'] = '${PORT}';
\$_SERVER['HTTP_HOST'] = 'localhost:${PORT}';
\$_SERVER['QUERY_STRING'] = '${(uri.split('?')[1] || '').replace(/'/g, "\\'")}';
\$_SERVER['REMOTE_ADDR'] = '${req.ip || '127.0.0.1'}';
\$_SERVER['PHP_SELF'] = '/index.php';

\$_GET = json_decode('${JSON.stringify(req.query || {}).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}', true) ?: [];
\$_POST = json_decode('${JSON.stringify(req.body || {}).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}', true) ?: [];
\$_REQUEST = array_merge(\$_GET, \$_POST);
\$_FILES = [];
\$_COOKIE = [];
\$_SESSION = json_decode('${JSON.stringify(sessionData).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\n/g, '\\n').replace(/\r/g, '')}', true) ?: [];
if (!is_array(\$_SESSION)) \$_SESSION = [];

putenv('APP_ENV=development');
putenv('APP_DEBUG=true');
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=database/kangguircm.sqlite');
putenv('SESSION_LIFETIME=120');
putenv('CSRF_TOKEN_NAME=csrf_token');
putenv('UPLOAD_PATH=public/uploads');
putenv('MAX_UPLOAD_SIZE=10485760');
\$_ENV['APP_ENV'] = 'development';
\$_ENV['APP_DEBUG'] = 'true';
\$_ENV['DB_DRIVER'] = 'sqlite';
\$_ENV['DB_PATH'] = 'database/kangguircm.sqlite';
\$_ENV['SESSION_LIFETIME'] = '120';
\$_ENV['CSRF_TOKEN_NAME'] = 'csrf_token';
\$_ENV['UPLOAD_PATH'] = 'public/uploads';
\$_ENV['MAX_UPLOAD_SIZE'] = '10485760';

chdir('${PROJECT_ROOT}');
require '${path.join(PROJECT_ROOT, 'public', 'index.php')}';
`;
}

// Main handler - catch all routes through PHP
app.all('*', async (req, res) => {
  try {
    const php = await initPHP();
    const phpCode = buildPhpRequest(req);
    const result = await php.runStream({ code: phpCode });
    let output = await result.stdoutText;
    const errors = await result.stderrText;

    if (errors && errors.trim()) {
      console.error('PHP stderr:', errors.substring(0, 500));
    }

    // ALWAYS extract session data first (before any redirect handling)
    const sessionMatch = output.match(/<!-- SESSION_DATA:(.*?):SESSION_DATA -->/s);
    if (sessionMatch && req.session) {
      try {
        const sessionData = JSON.parse(sessionMatch[1]);
        // Clear old PHP session keys
        for (const k of Object.keys(req.session)) {
          if (k === 'cookie') continue;
          delete req.session[k];
        }
        // Set new session data
        for (const [k, v] of Object.entries(sessionData)) {
          req.session[k] = v;
        }
      } catch (e) { console.error('Session parse error:', e.message); }
    }

    // Remove session data marker from output
    output = output.replace(/<!-- SESSION_DATA:.*?:SESSION_DATA -->/gs, '');

    // Extract redirect
    const redirectMatch = output.match(/<!-- REDIRECT:(.*?) -->/);
    if (redirectMatch) {
      const url = redirectMatch[1];
      // Save session then redirect
      req.session.save(() => {
        res.redirect(url);
      });
      return;
    }

    // Set content type
    if (!res.getHeader('content-type')) {
      if (output.trim().startsWith('{')) {
        res.setHeader('Content-Type', 'application/json');
      } else {
        res.setHeader('Content-Type', 'text/html; charset=UTF-8');
      }
    }

    res.status(200);
    req.session.save(() => {
      res.send(output);
    });

  } catch (err) {
    console.error('Server error:', err.message);
    res.status(500).send(`<pre style="font-family:monospace;padding:20px;background:#fee;color:#900;">
Server Error: ${err.message}
${err.stack}
</pre>`);
  }
});

initPHP().then(() => {
  app.listen(PORT, '0.0.0.0', () => {
    console.log(`\n🚀 Multi-Module Platform running at http://localhost:${PORT}`);
    console.log(`📋 Login: admin@example.com / admin123`);
    console.log(`🔧 PHP 8.3 + SQLite via WebAssembly\n`);
  });
}).catch(err => {
  console.error('Fatal:', err);
  process.exit(1);
});
