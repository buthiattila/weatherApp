const cityCache = {};
const cityCoordinatesCache = {};

export const getCachedCities = (country) => cityCache[country];
export const setCachedCities = (country, cities) => cityCache[country] = cities;
export const getCachedCoordinates = (key) => cityCoordinatesCache[key];
export const setCachedCoordinates = (key, coords) => cityCoordinatesCache[key] = coords;

export const getLocalStorage = (key) => {
	const data = localStorage.getItem(key);
	return data ? JSON.parse(data) : null;
};

export const setLocalStorage = (key, value) => {
	localStorage.setItem(key, JSON.stringify(value));
};
