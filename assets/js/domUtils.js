export const showLoading = () => $('#loading').fadeIn();
export const hideLoading = () => $('#loading').fadeOut();

export const handleError = (message) => {
    $('#cityInfo').append(
        $('<div>', {id: 'errorMsg', class: 'alert alert-danger', text: message})
    );

    setTimeout(() => {
        $('#errorMsg').fadeOut(function () {
            $(this).remove();
        });
    }, 3000);

    hideLoading();
};

export const initSelect2 = ($el, placeholder) => {
    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
    $el.select2({placeholder, allowClear: true});
};

export const prepareChartAndTable = () => {
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
