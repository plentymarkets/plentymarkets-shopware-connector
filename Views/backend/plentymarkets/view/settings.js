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

	initComponent: function()
	{
		var me = this;

		me.registerEvents();
		me.callParent(arguments);
	},

	/**
	 * Registers additional component events.
	 */
	registerEvents: function()
	{
		this.addEvents('save');
		this.addEvents('refresh');
	},

	build: function()
	{
		var me = this;
		if (me.isBuilt == true)
		{
			return;
		}
		me.setLoading(true);
		me.store = Ext.create('Shopware.apps.Plentymarkets.store.settings.Batch');
		me.store.load(function(data)
		{
			data = data[0]
			me.stores.warehouses = data.getWarehouses();
			me.stores.producers = data.getProducers();
			me.stores.orderStatus = data.getOrderStatus();
			me.stores.orderReferrer = data.getOrderReferrer();

			me.add(me.getFieldSets())
			me.addDocked(me.createToolbar());
			me.loadRecord(me.settings);
			me.isBuilt = true;
			me.setLoading(false);
		});
	},

	loadStores: function()
	{
		var me = this;
		me.setLoading(true);
		me.store.load({
			params: {
				refresh: true
			},
			callback: function(data)
			{
				data = data[0]

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
	createToolbar: function()
	{
		var me = this;

		return Ext.create('Ext.toolbar.Toolbar', {
			cls: 'shopware-toolbar',
			dock: 'bottom',
			ui: 'shopware-ui',
			items: ['->', {
				xtype: 'button',
				text: '{s name=plentymarkets/view/settings/button/refresh}plentymarkets Daten neu abrufen{/s}',
				cls: 'secondary',
				handler: function()
				{
					me.fireEvent('refresh', me);
				}
			}, {
				xtype: 'button',
				text: '{s name=plentymarkets/view/settings/button/save}Speichern{/s}',
				cls: 'primary',
				handler: function()
				{
					me.fireEvent('save', me);
				}
			}]
		});
	},

	getFieldSets: function()
	{
		var me = this;
		
		var paymentStatusStore = Ext.create('Shopware.apps.Base.store.PaymentStatus').load();
	
		return [{
			xtype: 'fieldset',
			title: 'Import Artikelstammdaten',
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
			items: [{
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemWarehouseID}plentymarkets Lager{/s}',
				name: 'ItemWarehouseID',
				id: 'plenty-ItemWarehouseID',
				store: me.stores.warehouses,
				supportText: 'Datenquelle für den Warenbestandsabgleich.'
			}, {
				xtype: 'slider',
				increment: 1,
				minValue: 0,
				maxValue: 100,
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/Warenbestandspuffer}Warenbestandspuffer{/s}',
				name: 'ItemWarehousePercentage',
				supportText: 'Prozentualer Anteil des netto-Warenbestandes des gewählten Lagers, welcher an shopware übertragen wird.',
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemProducerID}Hersteller{/s}',
				name: 'ItemProducerID',
				id: 'plenty-ItemProducerID',
				store: me.stores.producers,
				supportText: 'Sofern bei Artikeln in plentymarkets kein Hersteller zugeordnet wurde, wird dieser Hersteller in shopware mit den betreffenden Artikeln verknüpft.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemImageSyncActionID}Bilder synchronisieren{/s}',
				name: 'ItemImageSyncActionID',
				xtype: 'checkbox',
				inputValue: 1,
				uncheckedValue: '0',
				supportText: 'Aktivieren, wenn die Bilder von bestehenden Artikel synchronisiert werden sollen. Anderfalls werden Bilder nicht bei der Synchronisation berücksichtigt.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemCategorySyncActionID}Kategorien synchronisieren{/s}',
				name: 'ItemCategorySyncActionID',
				xtype: 'checkbox',
				inputValue: 1,
				uncheckedValue: '0',
				supportText: 'Aktivieren, wenn die Kategorien von bestehenden Artikel synchronisiert werden sollen. Anderfalls werden Kategorien nicht bei der Synchronisation berücksichtigt.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemNumberImportActionID}Nummern übernehmen{/s}',
				name: 'ItemNumberImportActionID',
				xtype: 'checkbox',
				inputValue: 1,
				uncheckedValue: '0',
				supportText: 'Aktivieren, wenn die Artikelnummern von plentymarkets übernommen werden sollen.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/DefaultCustomerGroupKey}Standard-Kundenklasse{/s}',
				name: 'DefaultCustomerGroupKey',
				store: Ext.create('Shopware.apps.Base.store.CustomerGroup').load(),
				valueField: 'key',
				supportText: 'Kundenklasse deren Preise von plentymarkerts zu shopware übertragen werden.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemCleanupActionID}Bereinigen{/s}',
				name: 'ItemCleanupActionID',
				store: new Ext.data.ArrayStore({
					fields: ['id', 'name'],
					data: [[1, 'Artikel deaktivieren'], [2, 'Artikel unwiederbringlich löschen']]
				}),
				supportText: 'Aktion die ausgeführt wird, wenn die Mandantenzuordnung bei plentymarkets gelöst wird oder kein Mapping für den Artikel vorhanden ist.'
			}

			]
		}, {
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
			items: [{
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderMarking1}Markierung{/s}',
				name: 'OrderMarking1',
				store: Ext.create('Shopware.apps.Plentymarkets.store.OrderMarking'),
				supportText: 'Sofern hier eine Auswahl getroffen wird, werden neue Aufträge von shopware an plentymarkets exportiert und dabei mit dieser Markierung versehen.',
				allowBlank: true,
				listConfig: {
					getInnerTpl: function(displayField)
					{
						return '{literal}<span style="padding: -3px; display: inline-block; width: 16px; height: 16px; margin-right: 3px;" class="plenty-OrderMarking-{id}"></span> {' + displayField + '}{/literal}';
					}
				}
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderReferrerID}Auftragsherkunft{/s}',
				name: 'OrderReferrerID',
				id: 'plenty-OrderReferrerID',
				store: me.stores.orderReferrer,
				supportText: 'Die hier ausgewählte Auftragsherkunft erhalten Aufträge von shopware in plentymarkets. In plentymarkets kann dazu eine eigene Auftragsherkunft angelegt werden.',
				allowBlank: true
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderPaidStatusID}Status bezahlt{/s}',
				name: 'OrderPaidStatusID',
				store: paymentStatusStore,
				supportText: 'shopware Status, der signalisiert, dass der Auftrag komplett bezahlt ist. Löst das Buchen des Zahlungseinganges bei plentymarkets aus.',
				displayField: 'description',
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderItemTextSyncActionID}Artikelbezeichnung übernehmen{/s}',
				name: 'OrderItemTextSyncActionID',
				xtype: 'checkbox',
				inputValue: 1,
				uncheckedValue: '0',
				supportText: 'Aktivieren, wenn die Bezeichnung der Artikel zu plentymarkets übertragen werden sollen. Anderfalls wird die in plentymarkets hinterlegte Bezeichnung verwendet.'
			}

			]
		}, {
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
			items: [{
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsID}Warenausgang{/s}',
				name: 'OutgoingItemsID',
				id: 'OutgoingItemsID',
				store: new Ext.data.ArrayStore({
					fields: ['id', 'name'],
					data: [[0, '---'], [1, 'heute gebucht']]
				}),
				supportText: 'Aufträge welche diese Regel erfüllen, werden von plentymarkets abgerufen, um die folgenden Statusänderungen in shopware zu bewirken.',
				allowBlank: true,
				listeners: {
					select: function(box)
					{
						if (box.getValue() > 0)
						{
							Ext.getCmp('plenty-OutgoingItemsOrderStatus').setValue(0);
							Ext.getCmp('plenty-OutgoingItemsOrderStatus').applyEmptyText();
						}
					}
				}
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsOrderStatus}Auftragsstatus{/s}',
				name: 'OutgoingItemsOrderStatus',
				id: 'plenty-OutgoingItemsOrderStatus',
				store: me.stores.orderStatus,
				supportText: 'Erreicht ein Auftrag in plentymarkets diesen Auftragsstatus, gilt dieser als versendet.',
				valueField: 'status',
				allowBlank: true,
				listeners: {
					select: function(box)
					{
						if (box.getValue() > 0)
						{
							Ext.getCmp('OutgoingItemsID').setValue(0);
							Ext.getCmp('OutgoingItemsID').applyEmptyText();
						}
					}
				}
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsIntervalID}Abfrageintervall{/s}',
				name: 'OutgoingItemsIntervalID',
				store: new Ext.data.ArrayStore({
					fields: ['id', 'name'],
					data: [[1, 'täglich, 12:00 Uhr'], [2, 'täglich, 18:00 Uhr'], [3, 'stündlich']]
				}),
				supportText: 'Zeitintervall für den Datenabgleich der Auftragsdaten.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsShopwareOrderStatusID}shopware Auftragsstatus{/s}',
				name: 'OutgoingItemsShopwareOrderStatusID',
				store: Ext.create('Shopware.apps.Base.store.OrderStatus').load(),
				displayField: 'description',
				supportText: 'Dieser Auftragsstatus wird gesetzt, wenn in plentymarkets der Warenausgang gebucht wurde.'
			}

			]
		}, {
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
			items: [{
				xtype: 'combo',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/IncomingPaymentShopwarePaymentFullStatusID}shopware Zahlungsstatus (komplett bezahlt){/s}',
				name: 'IncomingPaymentShopwarePaymentFullStatusID',
				store: paymentStatusStore,
				supportText: 'Zahlungsstatus, welche Aufträge erhalten, wenn diese innerhalb von plentymarkets als komplett bezahlt markiert werden.'
			}, {
				xtype: 'combo',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/IncomingPaymentShopwarePaymentPartialStatusID}shopware Zahlungsstatus (teilweise bezahlt){/s}',
				name: 'IncomingPaymentShopwarePaymentPartialStatusID',
				store: paymentStatusStore,
				supportText: 'Zahlungsstatus, welche Aufträge erhalten, wenn diese innerhalb von plentymarkets als teilweise bezahlt markiert werden.'
			}

			]
		}, {
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
			items: [{
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/InitialExportChunkSize}Paketgröße{/s}',
				name: 'InitialExportChunkSize',
				id: 'InitialExportChunkSize',
				store: new Ext.data.ArrayStore({
					fields: ['size'],
					data: [[10], [25], [50], [100], [250], [500], [1000], [2500], [5000]]
				}),
				displayField: 'size',
				supportText: 'Anzahl der Datensätze, die pro Durchlauf exportiert werden. Diese Einstellung betrifft Aktikel, Kunden und Attribute.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/InitialExportChunksPerRun}Pakete pro Durchlauf{/s}',
				name: 'InitialExportChunksPerRun',
				id: 'InitialExportChunksPerRun',
				store: new Ext.data.ArrayStore({
					fields: ['size', 'name'],
					data: [[-1, 'unendlich'], [2, '2'], [5, '5'], [10, '10'], [25, '25']]
				}),
				supportText: 'Anzahl der Datenpakete, die pro Durchlauf des Cronjobs exportiert werden sollen. Diese Einstellung betrifft <strong>nur</strong> Aktikel.'
			}]
		}, {
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
			items: [{
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ImportItemChunkSize}Paketgröße (Artikel){/s}',
				name: 'ImportItemChunkSize',
				id: 'ImportItemChunkSize',
				store: new Ext.data.ArrayStore({
					fields: ['size'],
					data: [[10], [25], [50], [100], [250], [500], [1000], [2500], [5000], [10000]]
				}),
				displayField: 'size',
				supportText: 'Anzahl der Artikel, die pro Durchlauf der Synchronisierung von plentymarkets abgerufen werden.'
			}]
		}];
	}

});
// {/block}
