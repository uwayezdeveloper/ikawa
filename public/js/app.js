// Ensure this is in global scope
window.showToast = function(message, type = 'info', heading = '') {
    $.toast({
        heading: heading,
        text: message,
        position: 'top-right',
        loaderBg: (type === 'success') ? '#00c292' : '#ff5b5b',
        icon: type,
        hideAfter: 3000,
        stack: 6
    });
};
