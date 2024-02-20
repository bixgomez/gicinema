// Helper function
let domReady = (cb) => {
  document.readyState === 'interactive' || document.readyState === 'complete'
    ? cb()
    : document.addEventListener('DOMContentLoaded', cb);
};

// Display body when DOM is loaded
domReady(() => {
  // document.body.style.visibility = 'visible';
  // document.body.style.opacity = '1';
  let element = document.getElementById("is_dom_ready");
  if (element) {
    element.style.backgroundColor = 'maroon';
  }
});
