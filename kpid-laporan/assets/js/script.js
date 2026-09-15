// Validasi Bootstrap bawaan + sedikit pengecekan tambahan
(function () {
  'use strict';

  var forms = document.querySelectorAll('.needs-validation');

  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  // Tampilkan nama file yang dipilih pada input file
  var fileInput = document.getElementById('bukti_file');
  var fileLabel = document.getElementById('bukti_file_label');
  if (fileInput && fileLabel) {
    fileInput.addEventListener('change', function () {
      if (fileInput.files.length > 0) {
        fileLabel.textContent = 'File dipilih: ' + fileInput.files[0].name;
      } else {
        fileLabel.textContent = '';
      }
    });
  }
})();
