// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Export}

/**
 * The export view initializes the two tab views continuous and initial. But the actual data
 * is loaded in the two tab views. It is extended by the Ext tab panel "Ext.tab.Panel".
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
Ext.define('Shopware.apps.Plentymarkets.view.Export', {

	extend: 'Ext.tab.Panel',

	alias: 'widget.plentymarkets-view-export',

	title: 'Datenaustausch',

	autoScroll: true,

	cls: 'shopware-form',

	initComponent: function()
	{
		var me = this;
		me.items = [{
			xtype: 'plentymarkets-view-dx-continuous'
		}, {
			xtype: 'plentymarkets-view-dx-initial',
		}];

		me.callParent(arguments);
	}

});
// {/block}
