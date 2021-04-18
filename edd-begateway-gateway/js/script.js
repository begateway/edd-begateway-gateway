/**
 * Creates a payment widget.
 * @see: https://docs.bepaid.by/en/widget/widget
 */
function edd_begateway_payment_widget() {
  var params = {
    checkout_url: edd_begateway_gateway_checkout_vars.checkout_url,
    token: edd_begateway_gateway_checkout_vars.token,
    closeWidget: function(status) {
      if (status == null) {
        window.location.replace(edd_begateway_gateway_checkout_vars.cancel_url);
      }
    }
  };

  new BeGateway(params).createWidget();
};

window.addEventListener('load',function(event){
  edd_begateway_payment_widget();
},false);
