// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/dx/Continuous}

/**
 * The continuous view builds the graphical elements and loads the data of the continuous data exchange.
 * It shows for example the activity status, the last export time and the next planed export time of orders.
 * It is extended by the Ext form panel "Ext.form.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.dx.Continuous', {

	extend: 'Ext.form.Panel',

	alias: 'widget.plentymarkets-view-dx-continuous',

	title: 'Kontinuierlicher Datenaustausch',

	autoScroll: true,

	cls: 'shopware-form',

	layout: 'anchor',

	border: false,

	defaults: {
		labelWidth: 155,
		anchor: '100%',
	},

	initComponent: function()
	{

		var me = this;
		me.store = Ext.create('Shopware.apps.Plentymarkets.store.dx.Continuous');

		me.items = [{
			xtype: 'fieldset',
			margin: 10,
			defaults: {
				margin: 6
			},
			title: 'Shopware \u2192 plentymarkets',
			items: [{
				xtype: 'gridpanel',
				store: me.store,
				title: '<b>Aufträge</b>',
				border: false,
				columns: [{
					header: 'Status',
					dataIndex: 'ExportOrderStatus',
					renderer: function(value)
					{
						if (value == 1)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-tick"></span>');
						}
						else if (value == 2)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-cross"></span> <b>Fehler</b>');
						}
						else if (value == 0)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-church"></span> <b>inaktiv</b>');
						}
					}
				}, {
					header: 'Letzter Export',
					flex: 3,
					dataIndex: 'ExportOrderLastRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}, {
					header: 'Nächster Export',
					flex: 3,
					dataIndex: 'ExportOrderNextRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}

				]
			}

			]
		}, {
			xtype: 'fieldset',
			margin: 10,
			defaults: {
				margin: 6
			},
			title: 'Shopware \u2190 plentymarkets',
			items: [

			{
				xtype: 'gridpanel',
				store: me.store,
				border: false,
				title: '<b>Artikelstammdaten</b>',
				columns: [{
					header: 'Status',
					dataIndex: 'ImportItemStatus',
					renderer: function(value)
					{
						if (value == 1)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-tick"></span>');
						}
						else if (value == 2)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-cross"></span> <b>Fehler</b>');
						}
						else if (value == 0)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-church"></span> <b>inaktiv</b>');
						}
					}
				}, {
					header: 'Letzter Import',
					flex: 3,
					dataIndex: 'ImportItemLastRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}, {
					header: 'Nächster Import',
					flex: 3,
					dataIndex: 'ImportItemNextRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}

				]

			}, {

				xtype: 'gridpanel',
				title: '<b>Artikelpreise</b>',
				store: me.store,
				border: false,

				columns: [{
					header: 'Status',
					dataIndex: 'ImportItemPriceStatus',
					renderer: function(value)
					{
						if (value == 1)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-tick"></span>');
						}
						else if (value == 2)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-cross"></span>');
						}
						else if (value == 0)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-church"></span> <b>inaktiv</b>');
						}
					}
				}, {
					header: 'Letzter Import',
					flex: 3,
					dataIndex: 'ImportItemPriceLastRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}, {
					header: 'Nächster Import',
					flex: 3,
					dataIndex: 'ImportItemPriceNextRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}

				]
			}, {
				xtype: 'gridpanel',
				store: me.store,
				title: '<b>Warenbestände</b>',
				border: false,
				columns: [{
					header: 'Status',
					dataIndex: 'ImportItemStockStatus',
					renderer: function(value)
					{
						if (value == 1)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-tick"></span>');
						}
						else if (value == 2)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-cross"></span> <b>Fehler</b>');
						}
						else if (value == 0)
						{
							return Ext.String.format('<span style="display: inline-block; height: 16px; width: 16px" class="sprite-church"></span> <b>inaktiv</b>');
						}
					}
				}, {
					header: 'Letzter Import',
					flex: 3,
					dataIndex: 'ImportItemStockLastRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}, {
					header: 'Nächster Import',
					flex: 3,
					dataIndex: 'ImportItemStockNextRunTimestamp',
					renderer: function(value)
					{
						return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
					}
				}

				]
			}]
		}

		];

		me.store.load({
			callback: function(records)
			{
				me.loadRecord(records[0]);
			}
		});

		me.callParent(arguments);
	}
});
// {/block}
