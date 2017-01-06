// {namespace name=backend/Plentymarkets/controller}
// {block name=backend/Plentymarkets/controller/mapping}

/**
 * Controller handling events of the mapping tab.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.controller.Mapping', {

    extend: 'Ext.app.Controller',

    init: function()
    {
        var me = this;

        me.control({
            'plentymarkets-view-mapping-main': {
                load : me.onLoadTabs,
                reload: function(view) {
                    me.onLoadTabs(view.panel, view.title, true);
                },
                save: me.onSave
            }
        });

        me.callParent(arguments);
    },

    onLoadTabs : function(view, currentTabTitle, fresh) {
        if (view.isBuilt && !fresh) {
            return;
        }

        view.setLoading(true);

        if (view.isBuilt) {
            view.removeAll();
            view.isBuilt = false;
        }

        var mappingInformationStore = Ext.create('Shopware.apps.Plentymarkets.store.mapping.Information');
        mappingInformationStore.proxy.extraParams = {
            fresh: !!fresh
        };
        mappingInformationStore.load(function(records, operation, success)
        {
            var currentTab = 0;

            Ext.Array.each(records, function(record)
            {
                var mapping = record.data;
                var objectType = mapping.objectType;

                var rows = mapping.destinationTransferObjects.map(function(object) {
                    var origin = mapping.originTransferObjects.find(function(originObject) {
                        return object.identifier == originObject.identifier;
                    });
                    var origName = (!!origin) ? origin.name : "";
                    var origId = (!!origin) ? origin.identifier : "";
                    return {
                        identifier: object.identifier,
                        name: object.name,
                        adapterName: mapping.destinationAdapterName,
                        originIdentifier: origId,
                        originName: origName,
                        originAdapterName: mapping.originAdapterName,
                        objectType : objectType
                    };
                });

                if (rows.length == 0) {
                    // There are no objects to be mapped
                    return;
                }

                var store = Ext.create('Shopware.apps.Plentymarkets.store.mapping.Row');
                store.loadData(rows);
                store.commitChanges();

                var tab = view.add({
                    xtype: 'plentymarkets-view-mapping-tab',
                    title: objectType,
                    store: store,
                    mapping: mapping,
                    panel: view
                });

                if (objectType == currentTabTitle) {
                    currentTab = tab;
                }
            });

            if (view.items != null && view.items.length > 0) {
                view.isBuilt = true;
                view.setActiveTab(currentTab);
            } else if (!success) {
                var message;
                if (typeof operation.error === 'string' || operation.error instanceof String) {
                    message = operation.error;
                } else {
                    message = operation.error.statusText;
                }

                Shopware.Notification.createGrowlMessage('Abrufen der Mapping Information fehlgeschlagen', message);
            }

            view.setLoading(false);
        });
    },

    onSave: function(view) {
        view.store.sync({
            failure : function(batch, options) {
                Ext.Msg.alert("Fehler", batch.proxy.getReader().jsonData.message);
            }
        });
    }
});
// {/block}
