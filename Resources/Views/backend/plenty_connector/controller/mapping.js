// {namespace name=backend/plentyconnector/controller}
// {block name=backend/plentyconnector/controller/mapping}

Ext.define('Shopware.apps.PlentyConnector.controller.Mapping', {
    extend: 'Ext.app.Controller',

    init: function () {
        var me = this;

        me.control({
            'plentymarkets-view-mapping-main': {
                load: me.onLoadTabs,
                reload: function (view) {
                    me.onLoadTabs(view.panel, view.title, true);
                },
                save: me.onSave
            }
        });

        me.callParent(arguments);
    },

    onLoadTabs: function (view, currentTabTitle, fresh) {
        if (view.isBuilt && !fresh) {
            return;
        }

        view.setLoading("{s name=plentyconnector/controller/mapping/loading}{/s}");

        if (view.isBuilt) {
            view.removeAll();
            view.isBuilt = false;
        }

        var mappingInformationStore = Ext.create('Shopware.apps.PlentyConnector.store.mapping.Information');

        mappingInformationStore.load(function (records, operation, success) {
            var currentTab = 0;

            Ext.Array.each(records, function (record) {
                var mapping = record.data;
                var objectType = mapping.objectType;

                var rows = mapping.destinationTransferObjects.map(function (object) {
                    var origin = mapping.originTransferObjects.find(function (originObject) {
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
                        objectType: objectType,
                        remove: false
                    };
                });

                if (rows.length == 0) {
                    // There are no objects to be mapped
                    return;
                }

                var store = Ext.create('Shopware.apps.PlentyConnector.store.mapping.Row');
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

                Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/mapping/mappingerror}{/s}', message);
            }

            view.setLoading(false);
        });
    },

    onSave: function (view) {
        view.store.sync({
            failure: function (batch, options) {
                Ext.Msg.alert("Fehler", batch.proxy.getReader().jsonData.message);
            },
            success: function (batch, options) {
                Shopware.Notification.createGrowlMessage("{s name=plentyconnector/controller/mapping/success1}{/s}", "{s name=plentyconnector/controller/mapping/success2}{/s}");
            }
        });
    }
});
// {/block}
