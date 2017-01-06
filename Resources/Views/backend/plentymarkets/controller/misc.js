// {namespace name=backend/Plentymarkets/controller}
// {block name=backend/Plentymarkets/controller/misc}

/**
 * Controller handling events of the misc tab.
 *
 * @author David Thulke <thulke@arvatis.com>
 */
Ext.define('Shopware.apps.Plentymarkets.controller.Misc', {
    extend: 'Ext.app.Controller',

    init: function () {
        var me = this;

        me.control({
            'plentymarkets-view-misc': {
                syncItem: me.onSyncItem
            }
        });

        me.callParent(arguments);
    },

    onSyncItem: function (view) {
        var message = 'Möchten Sie, dass der Artikel mit der von Ihnen eingegeben plentymarkets Artikel ID sofort abgeglichen wird?';

        Ext.Msg.confirm('Bestätigung erforderlich!', message, function (button) {
            if (button === 'yes') {
                Ext.Ajax.request({
                    url: '{url action=syncItem}',
                    success: function (response, options) {
                        var responseObject = Ext.decode(response.responseText);
                        if (responseObject.success) {
                            Shopware.Notification.createGrowlMessage('Aktion ausgeführt', 'Der Artikel wurde aktualisiert');
                        } else {
                            Shopware.Notification.createGrowlMessage('Aktion fehlgeschlagen', responseObject.message);
                        }
                    },
                    failure: function () {
                        // unexpected exception
                        Shopware.Notification.createGrowlMessage('Aktion fehlgeschlagen', 'Unbekannter Fehler');
                    },
                    jsonData: Ext.encode({
                        itemId: Ext.getCmp('plenty-itemId').value
                    })
                });
            }
        });
    }
});
// {/block}
