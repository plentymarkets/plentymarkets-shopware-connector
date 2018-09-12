// {namespace name=backend/plentyconnector/main}
// {block name=backend/plentyconnector/controller/actions}

Ext.define('Shopware.apps.PlentyConnector.controller.Actions', {
	extend: 'Ext.app.Controller',

	init: function () {
		var me = this;

		me.control({
			'plentymarkets-view-actions': {
				syncItem: me.onSyncItem
			}
		});

		me.callParent(arguments);
	},

	onSyncItem: function (view) {
		view.setLoading(true);

		var form = view.getForm();
		var itemId = form.findField("item_id").getValue();
		var message = '{s name=plentyconnector/controller/actions/item_import/confirm_text}{/s}'

		Ext.Msg.confirm('{s name=plentyconnector/controller/actions/item_import/confirm_title}{/s}', message, function (button) {
			if (button === 'no') {
				view.setLoading(false);
				return;
			}

			Ext.Ajax.request({
				url: '{url action=syncItem}',
				params: {
					item_id: form.findField("item_id").getValue()
				},
				success: function (response) {
					view.setLoading(false);

					response = Ext.decode(response.responseText);

					if (response.success) {
						Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/actions/item_import}{/s}', '{s name=plentyconnector/controller/actions/item_import/success}{/s}');
					} else {
						Shopware.Notification.createGrowlMessage('{s name=plentyconnector/controller/actions/item_import}{/s}', '{s name=plentyconnector/controller/actions/item_import/failed}{/s} ' + response.message);
					}
				}
			});
		});
	}
});
// {/block}
