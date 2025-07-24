import {
	getCachedCities,
	setCachedCities,
	getCachedCoordinates,
	setCachedCoordinates,
	getLocalStorage,
	setLocalStorage
} from './dataCache.js';

import {
	handleError,
	showLoading,
	hideLoading,
	initSelect2,
	prepareChartAndTable
} from './domUtils.js';

import {
	fetchCountries,
	fetchCities,
	getCityCoordinates as fetchCoordinates
} from './apiService.js';

const countryInputSelector = '#country';
const cityInputSelector = '#city';

export const initCountryInput = (countries) => {
	const $country = $(countryInputSelector).empty();

	const options = [
		$('<option>', {value: '', text: 'Kérjük, válasszon országot'}),
		...countries.map(({id, name}) => $('<option>', {value: name, text: name, 'data-id': id}))
	];

	$country.append(options);
	if ($.fn.select2) initSelect2($country, 'Ország kiválasztása');
};

export const initCityInput = (cities) => {
	const $citySelect = $(cityInputSelector);
	if ($citySelect.hasClass('select2-hidden-accessible')) $citySelect.select2('destroy');

	$citySelect.select2({
		placeholder: 'Város keresése',
		allowClear: true,
		minimumInputLength: 3,
		language: 'hu',
		data: [],
		ajax: {
			transport: ({data: {term}}, success) => {
				const results = cities
					.filter(city => city.toLowerCase().includes(term.toLowerCase()))
					.map(city => ({id: city, text: city}));
				success({results});
			},
			delay: 200
		}
	});
};

export const loadCountries = async () => {
	const localKey = 'countries';
	const fromLocal = getLocalStorage(localKey);

	if (fromLocal) return initCountryInput(fromLocal);

	try {
		showLoading();
		const {elements} = await fetchCountries();

		const countries = elements.map(({id, tags}) => ({
			id,
			name: tags["name:en"] || tags["name:eng"] || tags.name || '',
			iso: tags["ISO3166-1"] || tags["ISO3166-1:alpha2"] || ''
		})).filter(c => c.name.length)
			.sort((a, b) => a.name.localeCompare(b.name));

		setLocalStorage(localKey, countries);
		initCountryInput(countries);
	} catch {
		handleError('Nem sikerült betölteni az országokat.');
	} finally {
		hideLoading();
	}
};

export const loadCities = async (country) => {
	const $citySelect = $(cityInputSelector).prop('disabled', true).empty();
	const localKey = `cities_${country}`;
	const fromLocal = getLocalStorage(localKey);

	if (!country) {
		return $citySelect.val(null).trigger('change');
	}

	showLoading();

	if (getCachedCities(country)) {
		initCityInput(getCachedCities(country));
		$citySelect.prop('disabled', false);
		return hideLoading();
	}

	if (fromLocal) {
		setCachedCities(country, fromLocal);
		initCityInput(fromLocal);
		$citySelect.prop('disabled', false);
		return hideLoading();
	}

	try {
		const {data} = await fetchCities(country);
		const cities = Array.isArray(data) ? data : [];

		setCachedCities(country, cities);
		setLocalStorage(localKey, cities);
		initCityInput(cities);
		$citySelect.prop('disabled', false);
	} catch {
		handleError('Hiba történt a városok lekérésekor.');
		initCityInput([]);
	} finally {
		hideLoading();
	}
};

export const storeData = async () => {
	const country = $(countryInputSelector).val();
	const city = $(cityInputSelector).val();
	const frequency = $('#frequency').val();

	if (!country || !city) return;

	showLoading();
	const cacheKey = `${city.toLowerCase()}_${country.toLowerCase()}`;

	try {
		let result = getCachedCoordinates(cacheKey);

		if (!result) {
			result = await fetchCoordinates(city, country);
			if (result) setCachedCoordinates(cacheKey, result);
		}

		if (result) {
			const {osmId, lat, lon} = result;

			$.ajax({
				url: 'ajax/city/add',
				type: 'POST',
				data: {
					countryName: country,
					countryOsmId: $(countryInputSelector + ' option:selected').attr('data-id'),
					cityName: city,
					cityOsmId: osmId,
					lat: lat,
					lon: lon,
					frequency: frequency,
				},
				success: (response) => {
					if (response.hasOwnProperty('success') && response.success === true) {
						$(countryInputSelector).val(null).trigger('change');

						prepareChartAndTable();
					}
					else {
						handleError(obj.message);
					}
				},
				error: () => handleError('Nem sikerült lementeni az adatokat')
			});
		} else {
			handleError("Nem található ilyen város ebben az országban.");
		}
	} catch (err) {
		handleError("Hiba történt az adatok lekérdezésekor.", err);
	} finally {
		hideLoading();
	}
};
