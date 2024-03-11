function delete_posts() {
    let checkboxes = document.getElementsByClassName('post-checkbox')

    let to_be_deleted = []
    for (let i=0; i<checkboxes.length; i++) {
        let checkbox = checkboxes[i]
        if (checkbox.checked) {
            to_be_deleted.push(checkbox.id)
            checkbox.parentElement.parentElement.hidden = true
            checkbox.checked = false
        }
    }
    console.log(to_be_deleted)
    fetch('admin.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            delete_posts: 1,
            post_ids: to_be_deleted
        })
    })
}