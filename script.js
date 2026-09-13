// contact-script.js

// Ambil elemen-elemen yang dibutuhkan
const scriptURL = '#'; // Nanti bisa diganti dengan URL Google Sheets Apps Script jika ingin menyimpan data beneran
const form = document.forms['contact-form'];
const btnKirim = document.querySelector('.btn-kirim');
const btnLoading = document.querySelector('.btn-loading');
const myAlert = document.querySelector('.my-alert');

// Event Listener saat form disubmit
form.addEventListener('submit', (e) => {
  e.preventDefault(); // Mencegah reload halaman default

  // 1. Tampilkan tombol loading, hilangkan tombol kirim
  btnLoading.classList.toggle('d-none');
  btnKirim.classList.toggle('d-none');

  // 2. Simulasi pengiriman data (misal delay 1.5 detik)
  // Pada implementasi nyata, di sini Anda menggunakan fetch() ke API
  setTimeout(() => {
    
    // 3. Kembalikan tombol seperti semula
    btnLoading.classList.toggle('d-none');
    btnKirim.classList.toggle('d-none');

    // 4. Tampilkan Alert Sukses
    myAlert.classList.remove('d-none');

    // 5. Reset Form (kosongkan input)
    form.reset();

    // 6. Log ke console (untuk debugging)
    console.log('Pesan berhasil dikirim (Simulasi)');
    
  }, 1500);
});