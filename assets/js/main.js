import {loadCountries, loadCities, initCityInput, storeData} from './cityLogic.js';
import {prepareChartAndTable} from "./domUtils.js";

$(() => {
	loadCountries();
	initCityInput([]);
	prepareChartAndTable();

	$('body')
		.on('change', '#country', () => {
			$('#city').val(null).trigger('change');
			loadCities($('#country').val());
		})
		.on('change', cityInputSelector, () => {
			$('#frequency').prop('disabled', $('#city').val() == null);
		})
		.on('click', '.btn-process', storeData);
});
