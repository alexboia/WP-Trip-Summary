## Updating to 0.2.1
Due to the issue [described here](https://github.com/alexboia/WP-Trip-Summary/issues/29), it is recommended, regardless of your current plug-in version, that you update to 0.2.1, which fixes this issue. Here are the instructions on how to do so without losing any data.

### Manually Updating the Plug-in

1. Download the plug-in archive to a location on your local hard drive and unpack it;
2. Fire up an FTP client, such as Filezilla and connect to where your website is hosted;
3. Using the “Remote Site” panel, on the remote location your website is hosted, navigate to `/{root}/wp-content/plugins/wp-trip-summary` folder (where `{root}` is the folder that contains your entire website);
4. Using the “Local Site” panel, navigate to the plug-in folder you unpacked locally;
5. Copy over all the local plug-in file to the remote plug-in folder and overwrite all files.
6. Upon accessing your web-site, the plug-in will automatically perform the required adjustments.

![Manual Upload Plug-in Files](/screenshots/Capture_ManualUpload_Detailed.png?raw=true)

### Updating the Plugin Using the WordPress Plug-in Dashboard

1. Fire up an FTP client, such as Filezilla and connect to where your website is hosted;
2. Using the “Remote Site” panel, on the remote location your website is hosted, navigate to `/{root}/wp-content/plugins/wp-trip-summary/data` folder (where `{root}` is the folder that contains your entire website);
3. Using the “Local Site” panel, navigate to a folder you can use for temporary backup storage;
4. Download locally, from the `wp-trip-summary/data` (selected on step #2) folder, the `cache` and `storage` folders;

![Legacy Plug-In Data Files](/screenshots/Capture_LegacyFolders.png?raw=true)

5. Update the plug-in such as you normally would;
6. Using the “Remote Site” panel, on the remote location your website is hosted, navigate to `/{root}/wp-content/uploads/wp-trip-summary/tracks`;
7. From the local copy of the `storage` folder you just downloaded on step #4, upload all the contents to the remote `tracks` folder (selected on the previous step);
8. Using the “Remote Site” panel, on the remote location your website is hosted, navigate to `/{root}/wp-content/uploads/wp-trip-summary/cache`;
9. From the local copy of the `cache` folder you just downloaded on step #4, upload all the contents to the remote `cache` folder (selected on the previous step).

![New Plug-In Data Files](/screenshots/Capture_NewFolders.png?raw=true)