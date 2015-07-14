<?php
    defined('ABP01_LOADED') or die;
?>

<?php if ($data->settings->showTeaser && ($data->info->exists || $data->track->exists)): ?>
    <div id="abp01-techbox-teaser" class="abp01-techbox-teaser">
        <a id="abp01-techbox-teaser-action" href="javascript:void(0)"><?php echo $data->settings->topTeaserText; ?></a>
    </div>
<?php endif; ?>