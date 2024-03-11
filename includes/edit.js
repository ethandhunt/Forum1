function edit_comment(comment_id) {
    let edit_form = document.getElementById(`edit-comment-form-${comment_id}`)
    let prev_body = document.getElementById(`comment-body-${comment_id}`)

    edit_form.hidden = false
    prev_body.style.display = 'none'
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

    edit_form.hidden = false
    prev_body.style.display = 'none'
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