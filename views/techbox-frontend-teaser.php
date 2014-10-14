<?php
    defined('ABP01_LOADED') or die;
?>

<?php if ($data->info->exists || $data->track->exists): ?>
    <div id="abp01-techbox-teaser" class="abp01-techbox-teaser">
        <a id="abp01-techbox-teaser-action" href="javascript:void(0)"><?php echo __('For the pragmatic sort, there is also a trip summary at the bottom of this page. Click here to consult it', 'abp01-trip-summary'); ?></a>
    </div>
<?php endif; ?>