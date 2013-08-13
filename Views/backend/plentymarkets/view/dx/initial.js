// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/dx/Initial}

/**
 * The initial view builds the graphical elements and loads the data of the initial data export.
 * It shows for example the status of resources, which have to be exported, the start time and the finishing time data exports.
 * It is extended by the Ext grid panel "Ext.grid.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.dx.Initial', {

	extend: 'Ext.grid.Panel',

	alias: 'widget.plentymarkets-view-dx-initial',

	title: 'Initialer Export zu plentymarkets',

	autoScroll: true,

	cls: 'shopware-form',

	border: false,

	initComponent: function()
	{
		var me = this;

		var status = {
			open: 'offen',
			pending: 'wartend',
			running: 'läuft',
			success: 'fertig',
			error: 'Fehler',
		};

		var resourceNames = {
			ItemCategory: 'Kategorien',
			ItemAttribute: 'Attribute',
			ItemProperty: 'Eigenschaften/Merkmale',
			ItemProducer: 'Hersteller',
			Item: 'Artikel',
			Customer: 'Kunden',
		};

		me.store = Ext.create('Shopware.apps.Plentymarkets.store.Export').load();

		me.dockedItems = [Ext.create('Ext.toolbar.Toolbar', {
			cls: 'shopware-toolbar',
			dock: 'bottom',
			ui: 'shopware-ui',
			items: ['->', Ext.create('Ext.button.Button', {
				text: '{s name=plentymarkets/view/export/button/reload}Neu laden{/s}',
				cls: 'secondary',
				handler: function()
				{
					me.store.load()
				}
			})]
		})];

		me.columns = [{
			header: 'Resource',
			dataIndex: 'ExportEntityName',
			flex: 2,
			renderer: function(value)
			{
				return resourceNames[value];
			}
		}, {
			header: 'Status',
			dataIndex: 'ExportStatus',
			flex: 1,
			renderer: function(value)
			{
				return status[value];
			}

		}, {
			header: 'Start',
			xtype: 'datecolumn',
			dataIndex: 'ExportTimestampStart',
			flex: 1.5,
			renderer: function(value, x, record)
			{
				if (record.raw.ExportTimestampStart == -1)
				{
					return '–';
				}
				else
				{
					return Ext.util.Format.date(value, 'd.m.Y, H:i:s');
				}
			}
		}, {
			header: 'Fertig',
			xtype: 'datecolumn',
			dataIndex: 'ExportTimestampFinished',
			flex: 1.5,
			renderer: function(value, x, record)
			{
				if (record.raw.ExportTimestampFinished == -1)
				{
					return '–';
				}
				else
				{
					return Ext.util.Format.date(value, 'd.m.Y, H:i:s');
				}
			}
		}, {
			header: 'Aktion',
			xtype: 'actioncolumn',
			flex: 1,
			items: [{
				iconCls: 'plenty-export-start',
				tooltip: 'Vormerken',
				handler: function(grid, rowIndex, colIndex, item, eOpts, record)
				{
					me.fireEvent('handle', record, 'start');
				},

				getClass: function(value, metaData, record)
				{
					if (record.get('ExportStatus') != 'open')
					{
						return Ext.baseCSSPrefix + 'hidden';
					}
				}
			}, {
				iconCls: 'sprite-arrow-circle-double-135',
				tooltip: 'Erneut vormerken',
				handler: function(grid, rowIndex, colIndex, item, eOpts, record)
				{
					me.fireEvent('handle', record, 'restart');
				},

				getClass: function(value, metaData, record)
				{
					if (record.get('ExportStatus') != 'error' && record.get('ExportStatus') != 'success')
					{
						return Ext.baseCSSPrefix + 'hidden';
					}
				}
			}]
		}];

		me.callParent(arguments);
	}

});
// {/block}
