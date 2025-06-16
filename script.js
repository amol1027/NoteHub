// Get references to the input and list elements
const searchInput = document.getElementById('searchInput');
const itemList = document.getElementById('itemList');

// Add an event listener to the input field
searchInput.addEventListener('keyup', function() {
    const filter = searchInput.value.toLowerCase();
    const items = itemList.getElementsByTagName('li');

    // Loop through all list items and hide those that don't match the search query
    for (let i = 0; i < items.length; i++) {
        const item = items[i].textContent.toLowerCase();
        if (item.includes(filter)) {
            items[i].style.display = ""; // Show item
        } else {
            items[i].style.display = "none"; // Hide item
        }
    }
}); 