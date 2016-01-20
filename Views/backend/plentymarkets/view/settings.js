// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Settings}

/**
 * The settings view builds the graphical elements and loads all saved settings
 * data. It shows for example the chosen warehouse, the producer or the order
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
            me.stores.warehouses = data.getWarehouses();
            me.stores.producers = data.getProducers();
            me.stores.orderStatus = data.getOrderStatus();
            me.stores.orderReferrer = data.getOrderReferrer();
            me.stores.payments = data.getPayments();

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
                Ext.getCmp('plenty-ItemWarehouseID').bindStore(data.getWarehouses());
                Ext.getCmp('plenty-ItemProducerID').bindStore(data.getProducers());
                Ext.getCmp('plenty-OutgoingItemsOrderStatus').bindStore(data.getOrderStatus());
                Ext.getCmp('plenty-OrderReferrerID').bindStore(data.getOrderReferrer());

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
                        xtype: 'slider',
                        increment: 1,
                        minValue: 0,
                        maxValue: 100,
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/Warenbestandspuffer}Warenbestandspuffer{/s}',
                        name: 'ItemWarehousePercentage',
                        supportText: 'Prozentualer Anteil des netto-Warenbestandes des gewählten Lagers, welcher an shopware übertragen wird.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemProducerID}Hersteller{/s}',
                        name: 'ItemProducerID',
                        id: 'plenty-ItemProducerID',
                        store: me.stores.producers,
                        supportText: 'Sofern bei Artikeln in plentymarkets kein Hersteller zugeordnet wurde, wird dieser Hersteller in shopware mit den betreffenden Artikeln verknüpft.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemImageSyncActionID}Bilder synchronisieren{/s}',
                        name: 'ItemImageSyncActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Bilder von bestehenden Artikel synchronisiert werden sollen. Anderfalls werden Bilder nicht bei der Synchronisation berücksichtigt.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemImageAltAttributeID}Bilder alternativ Text{/s}',
                        name: 'ItemImageAltAttributeID',
                        supportText: 'Wählen Sie das Bild-Attribut aus, in dem der Alternativ-Text steht.',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                [1, 'Attribut 1'],
                                [2, 'Attribut 2'],
                                [3, 'Attribut 3']
                            ]
                        })
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemCategorySyncActionID}Kategorien synchronisieren{/s}',
                        name: 'ItemCategorySyncActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Kategorien von bestehenden Artikel synchronisiert werden sollen. Anderfalls werden Kategorien nicht bei der Synchronisation berücksichtigt.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemNumberImportActionID}Nummern übernehmen{/s}',
                        name: 'ItemNumberImportActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Artikelnummern von plentymarkets übernommen werden sollen.'
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
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemNameImportActionID}Produktname übernehmen{/s}',
                        name: 'ItemNameImportActionID',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                ['Name', 'Name'],
                                ['Name2', 'Name 2'],
                                ['Name3', 'Name 3']
                            ]
                        }),
                        supportText: 'Produktnamen aus plentymarkets wählen, welcher abgerufen werden soll.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemShortDescriptionImportActionID}Kurzbeschreibung übernehmen{/s}',
                        name: 'ItemShortDescriptionImportActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Kurzbeschreibung von plentymarkets übernommen werden soll.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemLongDescriptionImportActionID}Beschreibung übernehmen{/s}',
                        name: 'ItemLongDescriptionImportActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Produktbeschreibung von plentymarkets übernommen werden sollen.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemKeywordsImportActionID}Keywords übernehmen{/s}',
                        name: 'ItemKeywordsImportActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Keywords von plentymarkets übernommen werden sollen.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemFreetextsImportActionID}Freitextfelder übernehmen{/s}',
                        name: 'ItemFreetextsImportActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Freitextfelder von plentymarkets übernommen werden sollen.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemPriceImportActionID}Preis übernehmen{/s}',
                        name: 'ItemPriceImportActionID',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                ['Price', 'Preis'],
                                ['Price1', 'Preis 1'],
                                ['Price2', 'Preis 2'],
                                ['Price3', 'Preis 3'],
                                ['Price4', 'Preis 4'],
                                ['Price5', 'Preis 5'],
                                ['Price6', 'Preis 6'],
                                ['Price7', 'Preis 7'],
                                ['Price8', 'Preis 8'],
                                ['Price9', 'Preis 9'],
                                ['Price10', 'Preis 10'],
                                ['Price11', 'Preis 11'],
                                ['Price12', 'Preis 12'],
                            ]
                        }),
                        supportText: 'Preis aus plentymarkets wählen, der abgerufen werden soll.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemBundleHeadActionID}Artikelpaket-Artikel erstellen{/s}',
                        name: 'ItemBundleHeadActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Artikelpaket-Artikel (das eigentliche Paket) von plentymarkets als normaler Artikel in shopware angelegt werden soll. Dies ist <b>nicht</b> die Synchronisierung der Bundles!'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/DefaultCustomerGroupKey}Standard-Kundenklasse{/s}',
                        name: 'DefaultCustomerGroupKey',
                        store: Ext.create('Shopware.apps.Base.store.CustomerGroup').load(),
                        valueField: 'key',
                        supportText: 'Kundenklasse deren Preise von plentymarkerts zu shopware übertragen werden.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemCleanupActionID}Bereinigen{/s}',
                        name: 'ItemCleanupActionID',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                [1, 'Artikel deaktivieren'],
                                [2, 'Artikel unwiederbringlich löschen']
                            ]
                        }),
                        supportText: 'Aktion die ausgeführt wird, wenn die Mandantenzuordnung bei plentymarkets gelöst wird oder kein Mapping für den Artikel vorhanden ist.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemAssociateImportActionID}Zugehörige Daten synchronisieren{/s}',
                        name: 'ItemAssociateImportActionID',
                        store: new Ext.data.ArrayStore({
                            fields: ['id', 'name'],
                            data: [
                                [1, 'Eins pro Durchlauf'],
                                [2, 'Alle, bei jedem Duchlauf']
                            ]
                        }),
                        supportText: 'Zugehörige Daten sind Kategorien, Attribute, Merkmale/Eigenschaften und Hersteller. Legen Sie fest, ob pro Durchlauf alle Daten abgeglichen werden sollen, oder nur eine der genannten.'
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
                    },{
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderAdditionalCouponIdentifiers}Gutschein-Artikel{/s}',
                        name: 'OrderAdditionalCouponIdentifiers',
                        xtype: 'textfield',
                        supportText: 'Artikelnummern, welche als Gutschein zu plentymarkets übertragen werden. Getrennt durch |.',
                        allowBlank: true,
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderReferrerID}Auftragsherkunft{/s}',
                        name: 'OrderReferrerID',
                        id: 'plenty-OrderReferrerID',
                        store: me.stores.orderReferrer,
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
                    },
                    {
                        xtype: 'boxselect',
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderShopgateMOPIDs}Shopgate Zahlungsart(en){/s}',
                        name: 'OrderShopgateMOPIDs',
                        id: 'plenty-OrderShopgateMOPIDs',
                        store: me.stores.payments,
                        multiSelect: true,
                        allowBlank: true,
//                        value: me.settings.get('OrderShopgateMOPIDs'),
                        supportText: 'Wählen Sie die Shopgate Zahlungsarten aus (<b>Mehrfachauswahl möglich</b>). Aufträge, die diese Zahlungsart haben, werden in plentymarkets mit der Zahlungsart "Shopgate" angelegt.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderItemTextSyncActionID}Artikelbezeichnung übernehmen{/s}',
                        name: 'OrderItemTextSyncActionID',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0',
                        supportText: 'Aktivieren, wenn die Bezeichnung der Artikel zu plentymarkets übertragen werden sollen. Anderfalls wird die in plentymarkets hinterlegte Bezeichnung verwendet.'
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
            },
            {
                xtype: 'fieldset',
                title: 'Initialer Export',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    xtype: 'combo',
                    queryMode: 'local',
                    anchor: '100%',
                    displayField: 'name',
                    valueField: 'size',
                    allowBlank: false,
                    editable: true
                },
                items: [
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/InitialExportChunkSize}Paketgröße{/s}',
                        name: 'InitialExportChunkSize',
                        id: 'InitialExportChunkSize',
                        store: new Ext.data.ArrayStore({
                            fields: ['size'],
                            data: [
                                [10],
                                [25],
                                [50],
                                [100],
                                [250],
                                [500],
                                [1000],
                                [2500],
                                [5000]
                            ]
                        }),
                        displayField: 'size',
                        supportText: 'Anzahl der Datensätze, die pro Durchlauf exportiert werden. Diese Einstellung betrifft Aktikel, Kunden und Attribute.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/InitialExportChunksPerRun}Pakete pro Durchlauf{/s}',
                        name: 'InitialExportChunksPerRun',
                        id: 'InitialExportChunksPerRun',
                        store: new Ext.data.ArrayStore({
                            fields: ['size', 'name'],
                            data: [
                                [-1, 'unendlich'],
                                [2, '2'],
                                [5, '5'],
                                [10, '10'],
                                [25, '25']
                            ]
                        }),
                        supportText: 'Anzahl der Datenpakete, die pro Durchlauf des Cronjobs exportiert werden sollen. Diese Einstellung betrifft <strong>nur</strong> Aktikel.'
                    }
                ]
            },
            {
                xtype: 'fieldset',
                title: 'Synchronisierung',
                layout: 'anchor',
                defaults: {
                    labelWidth: 155,
                    xtype: 'combo',
                    queryMode: 'local',
                    anchor: '100%',
                    displayField: 'name',
                    valueField: 'size',
                    allowBlank: false,
                    editable: true
                },
                items: [
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/ImportItemChunkSize}Paketgröße (Artikel){/s}',
                        name: 'ImportItemChunkSize',
                        id: 'ImportItemChunkSize',
                        store: new Ext.data.ArrayStore({
                            fields: ['size'],
                            data: [
                                [10],
                                [25],
                                [50],
                                [100],
                                [250],
                                [500],
                                [1000],
                                [2500],
                                [5000],
                                [10000]
                            ]
                        }),
                        displayField: 'size',
                        supportText: 'Anzahl der Artikel, die pro Durchlauf der Synchronisierung von plentymarkets abgerufen werden.'
                    },
                    {
                        fieldLabel: '{s name=plentymarkets/view/settings/textfield/MayLogUsageData}Nutzungsdaten loggen{/s}',
                        name: 'MayLogUsageData',
                        id: 'MayLogUsageData',
                        xtype: 'checkbox',
                        inputValue: 1,
                        uncheckedValue: '0'
                    }
                ]
            }
        ];
    }

});
// {/block}
