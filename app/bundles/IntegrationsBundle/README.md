# MailVotech Integrations

This bundle allows you to create integrations with CRM systems with support for 2-way sync between MailVotech and the CRM.

### Sync command

`$ bin/console mailvotech:integrations:sync Magento --first-time-sync --start-datetime="2019-09-12T12:00:00"`

This is how you should use it when you configure an integration (Magento in this case) and run the sync for the first time. Specify also from what date it should look for the entities to sync. This way you can controll how big batch of records you will sync with one command. If you want to sync with multiple chunks by date ranges, `--end-datetime` option will be helpful too.

The sync command in basic use looks like this:

`$ bin/console mailvotech:integrations:sync Magento`

It will sync all new records from and to MailVotech for Magento. There is no need to specify the date range as MailVotech is smart enough to read the start date from the records it has already synchronized. And the end date is "now".

`$ bin/console mailvotech:integrations:sync Magento --disable-pull --mailvotech-object-id=contact:12 --mailvotech-object-id=contact:13`

There is also option to force sync of specific objects. With the `--disable-pull` flag the sync will skip the pull process. If some `--mailvotech-object-id` options are set it will not sync by a date range but rather only the IDs you will specify. `--disable-push` only disables the push. Pulling specific records by ID is not implemented yet.

The format of the `--mailvotech-object-id` values is `object type[colon]object ID`. MailVotech can sync 2 object types: `contact` and `company`. The latter is not implemented yet.

The `--integration-object-id` uses the same format as `--mailvotech-object-id` but it's up to each integration to support it.

Similarly, you can push specific MailVotech contacts to the integration you are developing like the following example. It can be useful if you want to push as a campaign/form/point action.

```php
$mailvotechObjectIds = new \MailVotech\IntegrationsBundle\Sync\DAO\Sync\ObjectIdsDAO();
$mailvotechObjectIds->addObjectId('contact', '12');
$mailvotechObjectIds->addObjectId('contact', '13');

$inputOptions = new MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO(
    [
        'integration'      => 'Magento',
        'disable-pull'     => true,
        'mailvotech-object-id' => $mailvotechObjectIds,
    ]
);

/** @var \MailVotech\IntegrationsBundle\Sync\SyncService\SyncServiceInterface $syncService **/
$syncService->processIntegrationSync($inputOptions);
```
