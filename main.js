const { app, BrowserWindow } = require('electron');
const http = require('http');
const path = require('path');

let mainWindow;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1500,
    height: 800,
    webPreferences: {
      nodeIntegration: true,
    },
  });

  const loadingHTML = `
    <html>
      <head>
        <style>
          body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
          }
          .message-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
          }
          .message-container h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
          }
          .message-container p {
            margin-top: 10px;
            font-size: 18px;
            color: #666;
          }
        </style>
      </head>
      <body>
        <div class="message-container">
          <h2>Bienvenido al Punto de Venta</h2>
          <p>Aguarde unos instantes, estamos conectando el sistema al servidor...</p>
        </div>
      </body>
    </html>
  `;

  mainWindow.loadURL(`data:text/html,${encodeURIComponent(loadingHTML)}`);

  // Intentar cargar la URL del servidor repetidamente
  checkServerAndLoadURL('http://localhost:8000', mainWindow);
}

function checkServerAndLoadURL(url, window) {
  const retryInterval = 3000; // 3 segundos

  function tryLoadURL() {
    http.get(url, (res) => {
      console.log(`Server response status: ${res.statusCode}`);
      if (res.statusCode === 200) {
        window.loadURL(url);
      } else {
        setTimeout(tryLoadURL, retryInterval);
      }
    }).on('error', (err) => {
      console.error(`Error connecting to server: ${err.message}`);
      setTimeout(tryLoadURL, retryInterval);
    });
  }

  tryLoadURL();
}

app.on('ready', () => {
  createWindow();
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});
