<script type="application/ld+json">
{
	"@context": "https://schema.org",
	"@type": "Place",
	"geo": {
		"@type": "GeoShape",
		"box": "<?php echo $data->southWest->getLatitude(); ?> <?php echo $data->southWest->getLongitude(); ?> <?php echo $data->northEast->getLatitude(); ?> <?php echo $data->northEast->getLongitude() ?>"
	},
	"name": "<?php echo esc_js($data->name); ?>"
}
</script>