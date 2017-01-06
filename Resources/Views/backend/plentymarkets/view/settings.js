// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Settings}

/**
 * The settings view builds the graphical elements and loads all saved settings
 * data. It shows for example the chosen warehouse, the manufacturer or the order
 * status. The settings are differentiated into four groups: "Import
 * Artikelstammdaten", "Export Aufträge", "Warenausgang", "Zahlungseingang bei
 * plentymarkets". It is extended by the Ext form panel "Ext.form.Panel".
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.Settings', {

    extend: 'Ext.form.Panel',

    alias: 'widget.plentymarkets-view-settings',

    title: '{s name=plentymarkets/view/settings/title}Einstellungen{/s}',

    autoScroll: true,

    cls: 'shopware-form',

    layout: 'anchor',

    border: false,

    isBuilt: false,

    stores: {},

    defaults: {
        anchor: '100%',
        margin: 10
    },

    initComponent: function () {
        var me = this;

        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function () {
        this.addEvents('save');
        this.addEvents('refresh');
        this.addEvents('test');
    },

    build: function () {
        var me = this;
        if (me.isBuilt == true) {
            return;
        }
        me.setLoading(true);
        me.store = Ext.create('Shopware.apps.Plentymarkets.store.settings.Batch');
        me.store.load(function (data) {
            data = data[0];
            me.stores.manufacturers = data.getManufacturers();
            me.stores.warehouses = data.getWarehouses();
            me.stores.orderReferrers = data.getOrderReferrers();

            me.add(me.getFieldSets());
            me.addDocked(me.createToolbar());
            me.loadRecord(me.settings);
            me.isBuilt = true;
            me.setLoading(false);
        });
    },

    loadStores: function () {
        var me = this;
        me.setLoading(true);
        me.store.load({
            params: {
                refresh: true
            },
            callback: function (data) {
                data = data[0];
                Ext.getCmp('plenty-ItemManufacturerID').bindStore(data.getManufacturers());
                Ext.getCmp('plenty-OrderReferrerID').bindStore(data.getOrderReferrers());
                Ext.getCmp('plenty-ItemWarehouseID').bindStore(data.getWarehouses());

                me.loadRecord(me.settings);

                me.setLoading(false);
            }
        });
    },

    /**
     * Creates the grid toolbar for the favorite grid
     *
     * @return Ext.toolbar.Toolbar
     */
    createToolbar: function () {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            cls: 'shopware-toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: ['->', {
                xtype: 'button',
                text: '{s name=plentymarkets/view/settings/button/test}Zugangsdaten Testen{/s}',
                cls: 'secondary',
                handler: function () {
                    me.fireEvent('test', me);
                }
            }, {
                xtype: 'button',
                text: '{s name=plentymarkets/view/settings/button/refresh}plentymarkets Daten neu abrufen{/s}',
                cls: 'secondary',
                handler: function () {
                    me.fireEvent('refresh', me);
                }
            }, {
                xtype: 'button',
                text: '{s name=plentymarkets/view/settings/button/save}Speichern{/s}',
                cls: 'primary',
                handler: function () {
                    me.fireEvent('save', me);
                }
            }]
        });
    },

    getFieldSets: function () {
        var me = this;

        var paymentStatusStore = Ext.create('Shopware.apps.Base.store.PaymentStatus').load();

        return [
            {
                xtype: 'fieldset',
                title: 'Zugangsdaten',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    anchor: '100%'
                },
                items: [

                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiUrl}URL{/s}',
                        helpText: 'Die URL muss mit <b>http://</b> oder <b>https://</b> beginnen.',
                        supportText: 'Tragen Sie hier die URL Ihres plentymarkets-Systems ein. Sie finden diese Information in der plentymarkets-Administration unter <b>Einstellungen » Grundeinstellungen » API-Daten » Host</b>.',
                        emptyText: 'http://www.ihr-plentymarkets-system.de/',
                        name: 'ApiUrl',
                        allowBlank: false
                    }, {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiUsernamex}Benutzername{/s}',
                        supportText: 'Der Benutzer sollte vom Typ <b>API</b> sein und nur für shopware verwendert werden. Achtung: Der Benutzer wird in Ihrem plentymarkets System unter <b>Einstellungen » Grundeinstellungen » Benutzer » Konten</b> angelegt!',
                        name: 'ApiUsername',
                        allowBlank: false
                    }, {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ApiPasswordx}Passwort{/s}',
                        supportText: 'Bitte vergeben Sie ein sicheres und starkes Passwort.',
                        name: 'ApiPassword',
                        allowBlank: false,
                        inputType: 'password'
                    }]
            },
            {
                xtype: 'fieldset',
                title: 'Import Artikeldaten',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    xtype: 'combo',
                    emptyText: '---',
                    queryMode: 'local',
                    anchor: '100%',
                    displayField: 'name',
                    valueField: 'id',
                    allowBlank: false,
                    editable: false
                },
                items: [
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemWarehouseID}plentymarkets Lager{/s}',
                        name: 'ItemWarehouseID',
                        id: 'plenty-ItemWarehouseID',
                        store: me.stores.warehouses,
                        supportText: 'Datenquelle für den Warenbestandsabgleich.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemConfiguratorSetType}Art des Konfigurators{/s}',
                        name: 'ItemConfiguratorSetType',
                        id: 'plenty-ItemConfiguratorSetType',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                [0, 'Default'],
                                [1, 'Auswahl'],
                                [2, 'Bild']
                            ]
                        }),
                        supportText: 'Wählen Sie das die Art des Konfigurators bei Varianten in Shopware aus.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemNumberSourceKey}Artikelnummer{/s}',
                        name: 'ItemNumberSourceKey',
                        supportText: 'Wählen Sie aus, welcher Wert von plentymarktes als Artikelnummer in Shopware verwendet werden soll.',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                ['ItemNo', 'Artikelnummer'],
                                ['EAN1', 'EAN 1'],
                                ['EAN2', 'EAN 2'],
                                ['EAN3', 'EAN 3'],
                                ['EAN4', 'EAN 4'],
                                ['ItemID', 'Artikel ID']
                            ]
                        })
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemVariationNumberSourceKey}Variantennummer{/s}',
                        name: 'ItemVariationNumberSourceKey',
                        supportText: 'Wählen Sie aus, welcher Wert von plentymarktes als Variantennummer in Shopware verwendet werden soll.',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                ['ColliNo', 'Variantennummer'],
                                ['EAN', 'EAN 1'],
                                ['EAN2', 'EAN 2'],
                                ['EAN3', 'EAN 3'],
                                ['EAN4', 'EAN 4']
                            ]
                        })
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: 'Export Aufträge',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    xtype: 'combo',
                    emptyText: '---',
                    queryMode: 'local',
                    anchor: '100%',
                    displayField: 'name',
                    valueField: 'id',
                    allowBlank: false,
                    editable: false
                },
                items: [
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderMarking1}Markierung{/s}',
                        name: 'OrderMarking1',
                        store: Ext.create('Shopware.apps.Plentymarkets.store.OrderMarking'),
                        supportText: 'Sofern hier eine Auswahl getroffen wird, werden neue Aufträge von shopware an plentymarkets exportiert und dabei mit dieser Markierung versehen.',
                        allowBlank: true,
                        listConfig: {
                            getInnerTpl: function (displayField) {
                                return '{literal}<span style="padding: -3px; display: inline-block; width: 16px; height: 16px; margin-right: 3px;" class="plenty-OrderMarking-{id}"></span> {' + displayField + '}{/literal}';
                            }
                        }
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderReferrerID}Auftragsherkunft{/s}',
                        name: 'OrderReferrerID',
                        id: 'plenty-OrderReferrerID',
                        store: me.stores.orderReferrers,
                        supportText: 'Die hier ausgewählte Auftragsherkunft erhalten Aufträge von shopware in plentymarkets. In plentymarkets kann dazu eine eigene Auftragsherkunft angelegt werden.',
                        allowBlank: true
                    },
                    {
                        xtype: 'boxselect',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderPaidStatusID}Status bezahlt{/s}',
                        name: 'OrderPaidStatusID',
                        store: paymentStatusStore,
                        id: 'plenty-OrderPaidStatusID',
//                        value: me.settings.get('OrderPaidStatusID'),
                        multiSelect: true,
                        supportText: 'shopware Status, der signalisiert, dass der Auftrag komplett bezahlt ist. Löst das Buchen des Zahlungseinganges bei plentymarkets aus.',
                        displayField: 'description'
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: 'Standard-Einträge',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    xtype: 'combo',
                    emptyText: '---',
                    queryMode: 'local',
                    anchor: '100%',
                    displayField: 'name',
                    valueField: 'id',
                    allowBlank: false,
                    editable: false
                },
                items: [
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemManufacturerID}Hersteller{/s}',
                        name: 'ItemManufacturerID',
                        id: 'plenty-ItemManufacturerID',
                        store: me.stores.manufacturers,
                        supportText: 'Sofern bei Artikeln in plentymarkets kein Hersteller zugeordnet wurde, wird dieser Hersteller in shopware mit den betreffenden Artikeln verknüpft.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/CustomerDefaultFormOfAddressID}Standard-Anrede{/s}',
                        name: 'CustomerDefaultFormOfAddressID',
                        supportText: 'Dieser Wert wird bei Kunden als Anrede exportiert, wenn diese nicht angegeben worden ist.',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                [0, 'Herr'],
                                [1, 'Frau'],
                                [2, 'Firma']
                            ]
                        })
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/CustomerDefaultStreet}Standard-Straße{/s}',
                        emptyText: 'z. B. keine Straße',
                        supportText: 'Dieser Wert wird bei Kunden als Straße exportiert, wenn diese nicht angegeben worden ist.',
                        name: 'CustomerDefaultStreet',
                        allowBlank: true
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/CustomerDefaultHouseNumber}Standard-Hausnr.{/s}',
                        emptyText: 'z. B. keine Hausnummer',
                        supportText: 'Dieser Wert wird bei Kunden als Hausnummer exportiert, wenn diese nicht angegeben worden ist.',
                        name: 'CustomerDefaultHouseNumber',
                        allowBlank: true
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/CustomerDefaultCity}Standard-Stadt{/s}',
                        emptyText: 'z. B. keine Stadt',
                        supportText: 'Dieser Wert wird bei Kunden als Stadt exportiert, wenn diese nicht angegeben worden ist.',
                        name: 'CustomerDefaultCity',
                        allowBlank: true
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/CustomerDefaultZipcode}Standard-PLZ{/s}',
                        emptyText: 'z. B. keine PLZ',
                        supportText: 'Dieser Wert wird bei Kunden als PLZ exportiert, wenn diese nicht angegeben worden ist.',
                        name: 'CustomerDefaultZipcode',
                        allowBlank: true
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: 'Warenausgang',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    xtype: 'combo',
                    emptyText: '---',
                    queryMode: 'local',
                    anchor: '100%',
                    displayField: 'name',
                    valueField: 'id',
                    allowBlank: false,
                    editable: false
                },
                items: [
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/checkbox/CheckOutgoingItems}Aktiv{/s}',
                        name: 'CheckOutgoingItems',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Deaktivieren Sie diese Funktion, um nur mit den Auftragsstatus zu arbeiten.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsID}Warenausgang{/s}',
                        name: 'OutgoingItemsID',
                        id: 'OutgoingItemsID',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                [0, '---'],
                                [1, 'heute gebucht']
                            ]
                        }),
                        supportText: 'Aufträge welche diese Regel erfüllen, werden von plentymarkets abgerufen, um die folgenden Statusänderungen in shopware zu bewirken.',
                        allowBlank: true,
                        listeners: {
                            select: function (box) {
                                if (box.getValue() > 0) {
                                    Ext.getCmp('plenty-OutgoingItemsOrderStatus').setValue(0);
                                    Ext.getCmp('plenty-OutgoingItemsOrderStatus').applyEmptyText();
                                }
                            }
                        }
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsOrderStatus}Auftragsstatus{/s}',
                        name: 'OutgoingItemsOrderStatus',
                        id: 'plenty-OutgoingItemsOrderStatus',
                        store: me.stores.orderStatus,
                        supportText: 'Erreicht ein Auftrag in plentymarkets diesen Auftragsstatus, gilt dieser als versendet.',
                        valueField: 'status',
                        allowBlank: true,
                        listeners: {
                            select: function (box) {
                                if (box.getValue() > 0) {
                                    Ext.getCmp('OutgoingItemsID').setValue(0);
                                    Ext.getCmp('OutgoingItemsID').applyEmptyText();
                                }
                            }
                        }
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsIntervalID}Abfrageintervall{/s}',
                        name: 'OutgoingItemsIntervalID',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                [1, 'täglich, 12:00 Uhr'],
                                [2, 'täglich, 18:00 Uhr'],
                                [3, 'stündlich']
                            ]
                        }),
                        supportText: 'Zeitintervall für den Datenabgleich der Auftragsdaten.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsShopwareOrderStatusID}shopware Auftragsstatus{/s}',
                        name: 'OutgoingItemsShopwareOrderStatusID',
                        store: Ext.create('Shopware.apps.Base.store.OrderStatus').load(),
                        displayField: 'description',
                        supportText: 'Dieser Auftragsstatus wird gesetzt, wenn in plentymarkets der Warenausgang gebucht wurde.'
                    }

                ]
            },
            {
                xtype: 'fieldset',
                title: 'Zahlungseingang bei plentymarkets',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    xtype: 'combo',
                    queryMode: 'local',
                    anchor: '100%',
                    emptyText: '---',
                    displayField: 'description',
                    valueField: 'id',
                    allowBlank: false,
                    editable: false
                },
                items: [
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/checkbox/CheckIncomingPayment}Aktiv{/s}',
                        name: 'CheckIncomingPayment',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Deaktivieren Sie diese Funktion, um nur mit den Auftragsstatus zu arbeiten.'
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/IncomingPaymentShopwarePaymentFullStatusID}shopware Zahlungsstatus (komplett bezahlt){/s}',
                        name: 'IncomingPaymentShopwarePaymentFullStatusID',
                        store: paymentStatusStore,
                        supportText: 'Zahlungsstatus, welche Aufträge erhalten, wenn diese innerhalb von plentymarkets als komplett bezahlt markiert werden.'
                    },
                    {
                        xtype: 'combo',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/IncomingPaymentShopwarePaymentPartialStatusID}shopware Zahlungsstatus (teilweise bezahlt){/s}',
                        name: 'IncomingPaymentShopwarePaymentPartialStatusID',
                        store: paymentStatusStore,
                        supportText: 'Zahlungsstatus, welche Aufträge erhalten, wenn diese innerhalb von plentymarkets als teilweise bezahlt markiert werden.'
                    }

                ]
            }
        ];
    }

});
// {/block}
