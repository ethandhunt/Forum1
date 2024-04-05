function edit_comment(comment_id) {
    let edit_form = document.getElementById(`edit-comment-form-${comment_id}`)
    let prev_body = document.getElementById(`comment-body-${comment_id}`)
    let img = document.getElementById(`comment-image-${comment_id}`)

    edit_form.hidden = false
    prev_body.style.display = 'none'
    img.style.display = 'none'
}

function delete_comment(comment_id) {
    if (confirm('delete this comment?')) {
        fetch(`view_post.php${document.location.search}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `delete_comment=1&comment_id=${comment_id}`
        }).then((res) => {
            console.log(`deleted comment_id ${comment_id}`)
            location.reload()
        })
    }
}

function edit_post() {
    let edit_form = document.getElementById(`edit-post-form`)
    let prev_body = document.getElementById(`post-body`)
    let img = document.getElementById(`post-image`)

    edit_form.hidden = false
    prev_body.style.display = 'none'
    img.style.display = 'none'
}

function delete_post() {
    if (confirm('delete this post?')) {
        fetch(`view_post.php${document.location.search}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `delete_post=1`
        }).then((res) => {
            console.log(`deleted post`)
            document.location.href = 'forum.php'
        })
    }
}

function block_user(user_id) {
    if (confirm("block this user?")) {
        fetch(`view_post.php${document.location.search}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `block_user=1&blocked_user_id=${user_id}`
        }).then((res) => {
            console.log(`blocked user_id ${user_id}`)
            document.location.href = 'forum.php'
        })
    }
}

function unblock_user(user_id) {
    if (confirm("unblock this user?")) {
        fetch(`view_post.php${document.location.search}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `unblock_user=1&blocked_user_id=${user_id}`
        }).then((res) => {
            console.log(`unblocked user_id ${user_id}`)
            location.reload()
        })
    }
}

function pin_post() {
    let pin_value = parseInt(document.getElementById("post-pinned").innerText)
    let new_pin_value = 1 - pin_value
    if (confirm(new_pin_value == 1 ? 'pin this post?' : 'unpin this post?')) {
        fetch(`view_post.php${document.location.search}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `pin_post=1&pin_value=${new_pin_value}`
        }).then((res) => {
            console.log(`pinned post`)
            document.location.href = 'forum.php'
        })
    }
}

function show_comment(comment_id) {
    let content = document.getElementById(`post-comment-${comment_id}`)
    let button = document.getElementById(`show-comment-${comment_id}`)
    
    if (content.style.display == "block") {
        content.style.display = "none"
        img.style.display = "none"
        button.textContent="Show Comment"
    } else {
        content.style.display = "block"
        img.style.display = "block"
        button.textContent="Hide Comment"
    }
}