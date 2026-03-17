const si = require('systeminformation');

// Definir los valores que deseas obtener
valueObject = {
    cpu: '*',
    osInfo: 'platform, release',
    system: 'model, manufacturer'
}

// Obtener la información
si.get(valueObject).then(data => console.log(data));
