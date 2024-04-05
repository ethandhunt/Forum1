document.addEventListener('input', function (event) {
	if (event.target.id !== 'sortby') return;
    // console.log(event.target.value)

    fetch(document.location, {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `sortby=1&sort=${event.target.value}`
    }).then((res) => {
        console.log(`sorted by ${event.target.value}`)
        location.reload()
    })
}, false);