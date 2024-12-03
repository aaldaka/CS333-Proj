// // JavaScript to toggle the sidebar visibility
// document.getElementById('toggle-btn').addEventListener('click', function() {
//     // Toggle the 'open' class to show or hide the sidebar
//     document.querySelector('.sidebar').classList.toggle('open');
    
//     // Optionally, toggle a margin on main content to show/hide based on the sidebar
//     const mainContent = document.querySelector('.main-content');
//     if (mainContent.style.marginLeft === '250px') {
//         mainContent.style.marginLeft = '0';
//     } else {
//         mainContent.style.marginLeft = '250px';
//     }
// });
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    sidebar.classList.toggle('open');
    mainContent.classList.toggle('shifted');
}

