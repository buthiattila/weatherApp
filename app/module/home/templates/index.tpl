<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Időjárás Monitor</title>
    <link href="assets/plugins/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="assets/plugins/select2/select2.min.css" type="text/css" rel="stylesheet">
    <link href="assets/css/main.css?v={$devVersion}" type="text/css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="card mt-2">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="country">Ország:</label>
                    <select id="country" class="form-control" autocomplete="off"></select>
                </div>
                <div class="col-md-4">
                    <label for="city">Város:</label>
                    <select id="city" name="city" class="form-control" autocomplete="off" disabled></select>
                </div>
                <div class="col-md-4">
                    <label for="frequency">Frissítési gyakoriság:</label>
                    <input type="text" class="form-control" id="frequency" autocomplete="off" placeholder="*/ * * * *" value="*/5 * * * *" disabled>
                </div>
            </div>
            <div class="mt-2 text-center">
                <button type="button" class="btn btn-success btn-process">Lekérdez</button>
            </div>
            <div class="mt-2" id="cityInfo">
                <div id="loading" style="display:none;">
                    <span class="spinner-border" role="status"></span> Betöltés...
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tb1">Adatgyűjtések</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tb2">Grafikon</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane container active" id="tb1">
                    <table class="mt-2 table table-striped" id="weatherTable">
                        <thead>
                        <tr>
                            <th>Ország</th>
                            <th>Város</th>
                            <th>Legfrissebb hőmérséklet (°C)</th>
                            <th>Frissítés ideje</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="tab-pane container fade" id="tb2">
                    <canvas id="tempChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/select2/select2.min.js"></script>
<script src="assets/plugins/select2/i18n/hu.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="module" src="assets/js/main.js?v={$devVersion}"></script>
</body>
</html>
