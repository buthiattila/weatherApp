const cityCache = {};
const cityCoordinatesCache = {};
const countryInputSelector = '#country';
const cityInputSelector = '#city';
const frequencyInputSelector = '#frequency';
const loadingSelector = '#loading';

const showLoading = () => $(loadingSelector).fadeIn();
const hideLoading = () => $(loadingSelector).fadeOut();

const handleError = (message) => {
    $('#cityInfo').append($('<div>', {id: 'errorMsg', class: 'alert alert-danger', text: message}));

    setTimeout(function () {
        $('#errorMsg').fadeOut(function () {
            $(this).remove();
        });
    }, 3000);
    hideLoading();
};

const initSelect2 = ($el, placeholder) => {
    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
    $el.select2({placeholder, allowClear: true});
};

const loadCountries = () => {
    const localKey = 'countries';
    const fromLocal = localStorage.getItem(localKey);

    if (fromLocal) {
        initCountryInput(JSON.parse(fromLocal));
        return;
    }

    const query = `[out:json][timeout:50];relation["boundary"="administrative"]["admin_level"="2"];out tags;`;

    showLoading();

    $.ajax({
        url: 'https://overpass-api.de/api/interpreter',
        type: 'POST',
        data: {data: query},
        success: ({elements}) => {
            const countries = elements
                .map(({id, tags}) => ({
                    id,
                    name: tags["name:en"] || tags["name:eng"] || tags.name || '',
                    iso: tags["ISO3166-1"] || tags["ISO3166-1:alpha2"] || ''
                }))
                .filter(c => c.name.length)
                .sort((a, b) => a.name.localeCompare(b.name));

            localStorage.setItem(localKey, JSON.stringify(countries));
            initCountryInput(countries);
            hideLoading();
        },
        error: () => handleError('Nem sikerült betölteni az országokat.')
    });
};

const initCountryInput = countries => {
    const $countrySelect = $(countryInputSelector).empty();

    const options = [
        $('<option>', {value: '', text: 'Kérjük, válasszon országot'}),
        ...countries.map(({id, name}) => $('<option>', {value: name, text: name, 'data-id': id}))
    ];

    $countrySelect.append(options);
    if ($.fn.select2) initSelect2($countrySelect, 'Ország kiválasztása');
};

const loadCities = country => {
    const $citySelect = $(cityInputSelector).prop('disabled', true).empty();
    const localKey = `cities_${country}`;
    const fromLocal = localStorage.getItem(localKey);

    if (!country) return $citySelect.val(null).trigger('change');

    showLoading();

    if (cityCache[country]) {
        initCityInput(cityCache[country]);
        $citySelect.prop('disabled', false);
        return hideLoading();
    }

    if (fromLocal) {
        const cities = JSON.parse(fromLocal);
        cityCache[country] = cities;
        initCityInput(cities);
        $citySelect.prop('disabled', false);
        return hideLoading();
    }

    $.ajax({
        url: 'https://countriesnow.space/api/v0.1/countries/cities',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({country}),
        success: ({data}) => {
            const cities = Array.isArray(data) ? data : [];
            cityCache[country] = cities;
            localStorage.setItem(localKey, JSON.stringify(cities));
            initCityInput(cities);
            $citySelect.prop('disabled', false);
            hideLoading();
        },
        error: () => {
            handleError('Hiba történt a városok lekérésekor.');
            initCityInput([]);
        },
        complete: () => hideLoading
    });
};

const initCityInput = cities => {
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

const getCityCoordinates = async (cityName, countryName) => {
    const cacheKey = `${cityName.toLowerCase()}_${countryName.toLowerCase()}`;
    if (cityCoordinatesCache[cacheKey]) return [cityCoordinatesCache[cacheKey]];

    const query = encodeURIComponent(`${cityName}, ${countryName}`);
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}`;

    try {
        const response = await fetch(url, {
            headers: {
                'User-Agent': 'SybellTest/1.0 (your.email@example.com)'
            }
        });

        if (!response.ok) throw new Error(`HTTP hiba: ${response.status}`);

        const data = await response.json();
        if (!data.length) return null;

        const {lat, lon, osm_id} = data[0];
        const result = {osmId: osm_id, lat, lon};

        cityCoordinatesCache[cacheKey] = result;
        return [result];
    }
    catch (error) {
        throw error;
    }
};

const storeData = async () => {
    const country = $(countryInputSelector).val();
    const city = $(cityInputSelector).val();
    const frequency = $(frequencyInputSelector).val();

    if (!country || !city) return;

    showLoading();

    try {
        const result = await getCityCoordinates(city, country);

        if (result?.length) {
            const {osmId, lat, lon} = result[0];

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

                        initChart();
                    }
                    else {
                        handleError(obj.message);
                    }
                },
                error: () => handleError('Nem sikerült lementeni az adatokat')
            });
        }
        else {
            handleError("Nem található ilyen város ebben az országban.");
        }
    }
    catch (err) {
        handleError("Hiba történt az adatok lekérdezésekor.", err);
    }
    finally {
        hideLoading();
    }
};

const initChart = () => {
    const canvas = document.getElementById('tempChart');
    const ctx = canvas.getContext('2d');

    $.ajax({
        url: '/api/city/data',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            const labels = [];
            const datasets = [];
            const $tableBody = $('#weatherTable tbody');
            $tableBody.empty(); // táblázat ürítése

            response.cities.forEach((city, idx) => {
                const allData = city.data;

                if (allData.length === 0) {
                    $tableBody.append(`
                    <tr>
                      <td>${city.country}</td>
                    <td>${city.city}</td>
                    <td>-</td>
                    <td>még nem volt lekérdezve</td>
                    </tr>
                `);
                    return; // grafikonra nem kerül fel, kilépünk
                }

                const lastEntry = allData[allData.length - 1]; // legutóbbi adat
                const temperatures = allData.map(d => d.temperature);
                const timeLabels = allData.map(d => d.recorded_at);

                // Csak utolsó adatot adjuk a táblázathoz
                $tableBody.append(`<tr>
                    <td>${city.country}</td>
                    <td>${city.city}</td>
                    <td>${lastEntry.temperature.toFixed(1)}</td>
                    <td>${lastEntry.recorded_at}</td>
                </tr>`);

                // Összes adatot használjuk a grafikonhoz
                datasets.push({
                    label: city.city,
                    data: temperatures,
                    borderColor: getColor(idx),
                    tension: 0.2
                });

                if (labels.length === 0) {
                    labels.push(...timeLabels);
                }
            });

            if (canvas.chartInstance) {
                canvas.chartInstance.destroy();
            }

            canvas.chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {beginAtZero: false},
                            x: {title: {display: true, text: 'Időpont'}}
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Hőmérséklet alakulása minden városban'
                            }
                        }
                    }
                });

        },
        error: function () {
            handleError('Hiba történt az adatok betöltése közben.');
        }
    });

    // Segédfüggvény színekhez
    function getColor(i) {
        const colors = ['red', 'blue', 'green', 'orange', 'purple', 'brown', 'teal', 'magenta'];
        return colors[i % colors.length];
    }
};

$(() => {
    loadCountries();
    initCityInput([]);
    initChart();

    $('body')
        .on('change', countryInputSelector, () => {
            $(cityInputSelector).val(null).trigger('change');
            loadCities($(countryInputSelector).val());
        })
        .on('change', cityInputSelector, () => {
            $(frequencyInputSelector).prop('disabled', $(cityInputSelector).val() == null);
        })
        .on('click', '.btn-process', storeData);
});