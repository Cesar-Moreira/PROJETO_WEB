<?php
require_once __DIR__ . '/../config/auth.php';
exigirLogin();

$tituloPagina = 'Mapa - CRUD Mundo';

// Configurações lidas do .env (a chave da API nunca fica escrita no código)
$chaveApi  = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';
$endereco  = $_ENV['MAPA_ENDERECO'] ?? 'Av. Salmão, 570 - Parque Residencial Aquarius, São José dos Campos - SP, 12246-260';
$nomeLocal = $_ENV['MAPA_NOME'] ?? 'ETEC São José dos Campos';

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<div class="conteudo">
    <div class="conteudo-principal">
        <div class="pagina-titulo">
            <h1><i class="fa-solid fa-location-dot"></i> Localização da <?= htmlspecialchars($nomeLocal) ?></h1>
        </div>

        <p style="color:#757575; margin-top:-0.5rem;">
            <i class="fa-solid fa-map-pin" style="color:var(--azul);"></i> <?= htmlspecialchars($endereco) ?>
        </p>

        <div class="tabela-container" style="padding:0.5rem;">
            <?php if ($chaveApi !== ''): ?>
                <div id="mapa" style="width:100%; height:450px; border-radius:8px;"></div>
                <script>
                    // Transforma o endereço em coordenadas (Geocoder) e marca no mapa
                    function iniciarMapa() {
                        var endereco = <?= json_encode($endereco, JSON_UNESCAPED_UNICODE) ?>;
                        var titulo = <?= json_encode($nomeLocal, JSON_UNESCAPED_UNICODE) ?>;
                        var mapa = new google.maps.Map(document.getElementById('mapa'), {
                            zoom: 16,
                            center: { lat: -23.2237, lng: -45.9009 } // provisório: centro de São José dos Campos
                        });
                        new google.maps.Geocoder().geocode({ address: endereco }, function (resultados, status) {
                            if (status === 'OK') {
                                mapa.setCenter(resultados[0].geometry.location);
                                var marcador = new google.maps.Marker({ map: mapa, position: resultados[0].geometry.location, title: titulo });
                                new google.maps.InfoWindow({ content: '<strong>' + titulo + '</strong><br>' + endereco }).open(mapa, marcador);
                            } else {
                                document.getElementById('mapa').innerHTML =
                                    '<p style="padding:1rem;">Não foi possível localizar o endereço (' + status + '). Verifique se a Geocoding API está ativada na sua chave.</p>';
                            }
                        });
                    }
                </script>
                <script async src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode($chaveApi) ?>&callback=iniciarMapa"></script>
            <?php else: ?>
                <!-- Sem chave de API: usa o mapa incorporado do Google (não exige chave) -->
                <iframe title="Mapa" width="100%" height="450" style="border:0; border-radius:8px;" loading="lazy"
                        allowfullscreen referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=<?= urlencode($endereco) ?>&output=embed"></iframe>
                <p style="font-size:0.8rem; color:#888; padding:0.5rem;">
                    Modo sem chave de API. Para usar a Google Maps JavaScript API, preencha <code>GOOGLE_MAPS_API_KEY</code> no <code>.env</code>.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
