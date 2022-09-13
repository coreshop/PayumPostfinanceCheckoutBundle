pimcore.registerNS('coreshop.provider.gateways.postfinance_checkout');

coreshop.provider.gateways.postfinance_checkout = Class.create(coreshop.provider.gateways.abstract, {
  getLayout: function (config) {
    return [
      {
        xtype: 'textfield',
        fieldLabel: t('postfinance_checkout.config.user_id'),
        name: 'gatewayConfig.config.user_id',
        length: 255,
        value: config.user_id ? config.user_id : '',
      },
      {
        xtype: 'textfield',
        fieldLabel: t('postfinance_checkout.config.space_id'),
        name: 'gatewayConfig.config.space_id',
        length: 255,
        value: config.space_id ? config.space_id : '',
      },
      {
        xtype: 'textfield',
        fieldLabel: t('postfinance_checkout.config.secret'),
        name: 'gatewayConfig.config.secret',
        length: 255,
        value: config.secret ? config.secret : '',
      },
    ];
  },
});
