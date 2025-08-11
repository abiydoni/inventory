// --- FILE: assets/js/app.js ---
// Small helper functions

/**
 * Kirim form menggunakan fetch API
 * @param {string} url - Alamat file PHP tujuan
 * @param {FormData} data - Data form yang akan dikirim
 * @param {function} callback - Fungsi callback saat response sukses
 */
function fetchForm(url, data, callback) {
  fetch(url, {
    method: "POST",
    body: data,
  })
    .then((r) => r.json())
    .then(callback)
    .catch((e) => Swal.fire("Error", e.message, "error"));
}

/**
 * Helper untuk menampilkan konfirmasi SweetAlert
 * @param {string} title
 * @param {string} text
 * @param {function} onConfirm
 */
function confirmAction(title, text, onConfirm) {
  Swal.fire({
    title: title,
    text: text,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ya",
  }).then((result) => {
    if (result.isConfirmed) {
      onConfirm();
    }
  });
}
