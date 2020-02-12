$(document).ready(function() {
  if (window.innerWidth < 768) {
    $('select[data-combobox=1]').select2({
      minimumResultsForSearch: Infinity
    });

    $('select[data-combobox=1]').each(function(index, element){
      element.style.pointerEvents = 'none';
    });

    $('select[data-combobox=1]').on('select2:open', function(){
      $('.select2-search__field').prop('disabled', true);
    });
  }
});
