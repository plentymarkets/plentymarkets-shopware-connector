// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Settings}
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
			me.stores.multishops = data.getMultishops();
			me.stores.orderStatus = data.getOrderStatus();
			me.stores.orderReferrer = data.getOrderReferrer();
			me.stores.categories = data.getCategories();

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
				me.stores.warehouses.loadData(data.getWarehouses());
				me.stores.producers.loadData(data.getProducers());
				me.stores.multishops.loadData(data.getMultishops());
				me.stores.orderStatus.loadData(data.getOrderStatus());
				me.stores.orderReferrer.loadData(data.getOrderReferrer());

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
				store: me.stores.warehouses,
				supportText: 'Welches Lager soll für die Aktualisierung der Warenbestände verwendet werden?'
			}, {
				xtype: 'slider',
				increment: 1,
				minValue: 0,
				maxValue: 100,
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/Warenbestandspuffer}Warenbestandspuffer{/s}',
				name: 'ItemWarehousePercentage',
				supportText: 'Wieviel Prozent des netto-Warenbestandes sollen in shopware gebucht werden?',
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/WebstoreID}Mandant (Shop){/s}',
				name: 'WebstoreID',
				store: me.stores.multishops,
				supportText: 'Stellen Sie hier eine Verknüfung zu dem in plentymarkets konfigurierten shopware-System her.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemProducerID}Hersteller{/s}',
				name: 'ItemProducerID',
				store: me.stores.producers,
				supportText: 'Welcher Hersteller soll den Artikeln zugeordnet werden, wenn bei plentymarkets keiner zugerordnet ist.'
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/ItemCategoryRootID}Kategorie Startknoten{/s}',
				name: 'ItemCategoryRootID',
				store: me.stores.categories
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/DefaultCustomerGroupKey}Standard-Kundenklasse{/s}',
				name: 'DefaultCustomerGroupKey',
				store: Ext.create('Shopware.apps.Base.store.CustomerGroup').load(),
				valueField: 'key'
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
				supportText: 'Wenn die exportierten Aufträge eine Markierung erhalten sollen, können Sie das hier einstellen.',
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
				store: me.stores.orderReferrer,
				supportText: 'Stellen Sie hier ein, welche Herkunft den exportierten Aufträgen zugeordnet werden soll.',
				allowBlank: true
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OrderPaidStatusID}Status bezahlt{/s}',
				name: 'OrderPaidStatusID',
				store: paymentStatusStore,
				supportText: 'Aufträge die dieses Status erreichen, werden bei plenty als bezahlt markiert und der Zahlungseingang gebucht.',
				displayField: 'description',
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
				store: Ext.create('Shopware.apps.Plentymarkets.store.outgoing_items.OutgoingItems').load(),
				supportText: 'Wann wurde der Warenausgang gebucht',
				allowBlank: true,
				listeners: {
					select: function(box)
					{
						if (box.getValue() > 0)
						{
							Ext.getCmp('OutgoingItemsOrderStatus').setValue(0);
							Ext.getCmp('OutgoingItemsOrderStatus').applyEmptyText();
						}
					}
				}
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsOrderStatus}Auftragsstatus{/s}',
				name: 'OutgoingItemsOrderStatus',
				id: 'OutgoingItemsOrderStatus',
				store: me.stores.orderStatus,
				supportText: 'Aufträge, die in plentymarkets dieses Stauts haben, werden in shopware als erledigt markiert.',
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
				store: Ext.create('Shopware.apps.Plentymarkets.store.outgoing_items.Interval').load(),
			}, {
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/OutgoingItemsShopwareOrderStatusID}Shopware Auftragsstatus{/s}',
				name: 'OutgoingItemsShopwareOrderStatusID',
				store: Ext.create('Shopware.apps.Base.store.OrderStatus').load(),
				displayField: 'description',
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
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/IncomingPaymentShopwarePaymentFullStatusID}shopware Zahlungsstatus (komplett bezhalt){/s}',
				name: 'IncomingPaymentShopwarePaymentFullStatusID',
				store: paymentStatusStore
			}, {
				xtype: 'combo',
				fieldLabel: '{s name=plentymarkets/view/settings/textfield/IncomingPaymentShopwarePaymentPartialStatusID}shopware Zahlungsstatus (teilweise bezhalt){/s}',
				name: 'IncomingPaymentShopwarePaymentPartialStatusID',
				store: paymentStatusStore
			}

			]
		}];
	}

});
// {/block}
