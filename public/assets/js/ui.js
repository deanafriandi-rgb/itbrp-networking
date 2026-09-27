document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('[data-demo]').forEach(el=>{
    el.addEventListener('click',()=>alert('Prototype UI: aksi ini nanti dihubungkan ke Laravel.'));
  });
});
