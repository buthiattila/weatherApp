export const fetchCountries = async () => {
	const query = `[out:json][timeout:50];relation["boundary"="administrative"]["admin_level"="2"];out tags;`;

	return $.ajax({
		url: 'https://overpass-api.de/api/interpreter',
		type: 'POST',
		data: {data: query}
	});
};

export const fetchCities = async (country) => {
	return $.ajax({
		url: 'https://countriesnow.space/api/v0.1/countries/cities',
		method: 'POST',
		contentType: 'application/json',
		data: JSON.stringify({country})
	});
};

export const getCityCoordinates = async (cityName, countryName) => {
	const query = encodeURIComponent(`${cityName}, ${countryName}`);
	const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}`;

	const response = await fetch(url, {
		headers: {
			'User-Agent': 'YourAppName/1.0 (your.email@example.com)'
		}
	});

	if (!response.ok) throw new Error(`HTTP hiba: ${response.status}`);

	const data = await response.json();
	if (!data.length) return null;

	const {lat, lon, osm_id} = data[0];
	return {osmId: osm_id, lat, lon};
};
