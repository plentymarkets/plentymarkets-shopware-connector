// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/mapping/Main}

/**
 * The /mapping/main view initializes the seven log grid view tabs and loads the mapping data.
 * Each tab contains two columns, the "Shopware" column and the "plentymarkets" column.
 * It is extended by the Ext tab panel "Ext.tab.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.mapping.Main', {

	extend: 'Ext.tab.Panel',

	alias: 'widget.plentymarkets-view-mapping-main',

	title: '{s name=plentymarkets/view/mappingtabs/title}Mapping{/s}',

	autoScroll: true,

	cls: 'shopware-form',

	layout: 'anchor',

	border: false,

	/**
	 * Init the main detail component, add components
	 *
	 * @return void
	 */
	initComponent: function()
	{
		var me = this, names = {};

		names['Currency'] = 'Währungen';
		names['Country'] = 'Länder';
		names['MeasureUnit'] = 'Einheiten';
		names['Vat'] = 'Steuersätze';
		names['ShippingProfile'] = 'Versandarten';
		names['MethodOfPayment'] = 'Zahlungsarten';
		names['CustomerClass'] = 'Kundengruppen';
		names['Referrer'] = 'Partner';

		Ext.create('Shopware.apps.Plentymarkets.store.mapping.Status').load(function(records)
		{
			Ext.Array.each(records, function(record)
			{

				me.add({
					xtype: 'plentymarkets-view-mapping-tab',
					title: names[record.get('name')],
					entity: record.get('name'),
					status: record,
					panel: me
				});

			});

			me.setActiveTab(0);

		});

		me.callParent(arguments);
	}

});
// {/block}
