<div id="swagger-ui"></div>
<div class="swagger-ui" id="formats">
    <div class="information-container wrapper">
        <div class="info">
            Other API docs:
            <a href="?doctype=redoc">ReDoc</a>
            <a href="?doctype=swagger">Swagger</a>
        </div>
    </div>
</div>
<script src="/swagger_bake/swagger-ui-bundle.js"> </script>
<script src="/swagger_bake/swagger-ui-standalone-preset.js"> </script>
<script>
    window.onload = function() {
        // Begin Swagger UI call region
        const ui = SwaggerUIBundle({
            url: '<?php echo $url ?>',
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            layout: "StandaloneLayout"
        })
        // End Swagger UI call region

        window.ui = ui
    }
</script>