// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/dx/Grid}

/**
 * The grid view builds the graphical grid elements and loads the logged data
 * like export messages, or SOAP-Call information. It is extended by the Ext
 * grid panel "Ext.grid.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.dx.Grid', {

	extend: 'Ext.grid.Panel',

	alias: 'widget.plentymarkets-view-dx-grid',

	autoScroll: true,

	border: false,

	cls: 'plenty-grid',

	features: [{
		ftype: 'grouping'
	}],

	initComponent: function()
	{
		var me = this;

		var snippet = {
			Item: 'Stammdaten',
			ItemStack: 'Stack',
			ItemBundle: 'Bundle (Pakete)',
			ItemStock: 'Warenbestände',
			ItemPrice: 'Preise',
			Order: 'Aufträge',
			OrderIncomingPayment: 'Zahlungseingänge',
			OrderOutgoingItems: 'Warenausgänge'
		};

		me.columns = [{
			header: 'Daten',
			dataIndex: 'Entity',
			renderer: function(value)
			{
				return snippet[value];
			},
			flex: 3
		}, {
			header: 'Status',
			dataIndex: 'Status',
			flex: 1,
			tdCls: 'plenty-td-icon',
			renderer: function(value, metaData, record)
			{
				if (value == 1)
				{
					return Ext.String.format('<div class="plenty-status plenty-status-ok">&nbsp;</div>');
				}
				else if (value == 2)
				{
					metaData.tdAttr = 'data-qtip="' + record.get('Error') + '"';
					return Ext.String.format('<div class="plenty-status plenty-status-error">&nbsp;</div>');
				}
				else if (value == 0)
				{
					metaData.tdAttr = 'data-qtip="Inaktiv"';
					return Ext.String.format('<div class="plenty-status plenty-status-dxc-inactive">&nbsp;</div>');
				}
			}
		}, {
			header: 'Letzter Import',
			flex: 7,
			dataIndex: 'LastRunTimestamp',
			renderer: function(value)
			{
				if (value)
				{
					return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
				}
				else
				{
					return '–';
				}
			}
		}, {
			header: 'Nächster Import',
			flex: 7,
			dataIndex: 'NextRunTimestamp',
			renderer: function(value)
			{
				if (value)
				{
					return Ext.util.Format.date(value, 'd.m.Y, H:i:s') + ' Uhr';
				}
				else
				{
					return '–';
				}
			}
		}];

		me.callParent(arguments);
	}

});
// {/block}
