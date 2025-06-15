<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mapa de Estaciones Valenbisi JDG</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      text-align: center;
      background-color: #f9f9f9;
    }
    h1 {
      color: green;
      font-size: 24px;
      margin-top: 20px;
    }
    #map {
      height: 600px;
      width: 100%;
      margin-top: 20px;
    }
    #filtro-container {
      margin-top: 10px;
    }
    label, select {
      font-size: 16px;
      margin-right: 10px;
    }
    select {
      appearance: none;
      background-color: white;
      border: 2px solid #4CAF50;
      border-radius: 8px;
      padding: 8px 12px;
      font-size: 16px;
      color: #333;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      background-image: url('data:image/svg+xml;utf8,<svg fill="%234CAF50" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 16px;
    }
    select:hover {
      border-color: #388E3C;
    }
    select:focus {
      outline: none;
      border-color: #2E7D32;
      box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
    }
    .lang-button {
      margin: 10px auto;
      display: inline-block;
      background-color: #45a049;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      cursor: pointer;
      border-radius: 5px;
    }
    .lang-button:hover {
      background-color: rgb(54, 115, 57);
    }
  </style>
</head>
<body>
  <h1 id="main-title">Mapeo de Bicicletas en Valencia</h1>

  <div>
    <button id="toggleLangBtn" class="lang-button">English</button>
  </div>

  <div id="filtro-container">
    <label id="filter-label" for="minDisponibles">Mostrar estaciones con al menos:</label>
    <select id="minDisponibles">
      <option value="0">0 bicicletas</option>
      <option value="5">5 bicicletas</option>
      <option value="10">10 bicicletas</option>
      <option value="15">15 bicicletas</option>
    </select>
  </div>

  <div id="map"></div>

  <script>
    var map = L.map('map').setView([39.47, -0.37], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const translations = {
      es: {
        disponibles: "Disponibles",
        libres: "Libres",
        total: "Total",
        label: "Mostrar estaciones con al menos:",
        bikes: "bicicletas",
        title: "Mapeo de Bicicletas en Valencia",
        button: "English"
      },
      en: {
        disponibles: "Available",
        libres: "Free",
        total: "Total",
        label: "Show stations with at least:",
        bikes: "bikes",
        title: "Bike Mapping in Valencia",
        button: "Español"
      }
    };

    let currentLang = 'es';
    let allStations = [];

    function getMarkerColor(available) {
      if (available < 5) return 'red';
      else if (available < 10) return 'orange';
      else if (available < 20) return 'yellow';
      else return 'green';
    }

    function updateTexts() {
      const t = translations[currentLang];
      document.getElementById('filter-label').textContent = t.label;
      document.getElementById('main-title').textContent = t.title;
      document.getElementById('toggleLangBtn').textContent = t.button;

      const select = document.getElementById('minDisponibles');
      for (let i = 0; i < select.options.length; i++) {
        const val = select.options[i].value;
        select.options[i].textContent = `${val} ${t.bikes}`;
      }
    }

    function renderStations(minAvailable) {
      map.eachLayer(layer => {
        if (layer instanceof L.CircleMarker) {
          map.removeLayer(layer);
        }
      });

      const t = translations[currentLang];
      allStations.forEach(station => {
        const { lat, lon, address, available, free, total } = station;
        if (lat && lon && available >= minAvailable) {
          L.circleMarker([lat, lon], {
            color: getMarkerColor(available),
            radius: 8,
            fillOpacity: 0.8
          })
          .addTo(map)
          .bindPopup(`
            <strong>${address}</strong><br>
            <b>${t.disponibles}:</b> ${available}<br>
            <b>${t.libres}:</b> ${free}<br>
            <b>${t.total}:</b> ${total}
          `);
        }
      });
    }

    fetch('data.json')
      .then(response => {
        if (!response.ok) throw new Error(`Error al cargar data.json: ${response.statusText}`);
        return response.json();
      })
      .then(data => {
        allStations = Object.values(data);
        renderStations(0);
      })
      .catch(error => {
        console.error('Error cargando los datos:', error);
      });

    document.getElementById('minDisponibles').addEventListener('change', e => {
      const min = parseInt(e.target.value);
      renderStations(min);
    });

    document.getElementById('toggleLangBtn').addEventListener('click', () => {
      currentLang = currentLang === 'es' ? 'en' : 'es';
      updateTexts();
      const min = parseInt(document.getElementById('minDisponibles').value);
      renderStations(min);
    });

    updateTexts(); 
  </script>
</body>
</html>
