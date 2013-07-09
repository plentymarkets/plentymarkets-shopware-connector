// {namespace name=backend/Plentymarkets/view}
// {block name=backend/Plentymarkets/view/Export}
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
