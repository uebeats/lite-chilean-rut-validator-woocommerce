(function ($) {
  $('body').on('blur change', '#billing_rut_lcrv', function () {

    $('#billing_rut_lcrv').rut();

    const wrapper = $(this).closest('.form-row');
    let rut = $(this).val();

    if ($.validateRut(rut)) {
      wrapper.addClass('woocommerce-validated');
    } else {
      wrapper.removeClass('woocommerce-validated');
      wrapper.addClass('woocommerce-invalid woocommerce-invalid-required-field');
    }
  });
})(jQuery);