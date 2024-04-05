function vote(post_id, type) {
    let id = `${type}vote-${post_id}`
    let opposite_type = type == 'up' ? 'down' : 'up'
    let opposite_id = `${opposite_type}vote-${post_id}`
    let vote_icon = document.getElementById(id)
    let opposite_vote_icon = document.getElementById(opposite_id)

    if (!vote_icon.classList.contains('can_vote')) {
        return
    }
    
    vote_icon.classList.remove('can_vote')
    opposite_vote_icon.classList.add('can_vote')
    
    let like_count = document.getElementById(`likes-${post_id}`)
    let current_likes = parseInt(like_count.innerText)
    let weight = type == 'up' ? 1 : -1
    like_count.innerText = current_likes + weight

    fetch("forum.php", {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        // body: form_data
        body: `vote=1&post_id=${post_id}&vote_type=${type}`
    }).then((res) => console.log(`${type}voted post_id ${post_id}`))
}

function vote_comment(comment_id, type) {
    let id = `${type}vote-${comment_id}`
    let opposite_type = type == 'up' ? 'down' : 'up'
    let opposite_id = `${opposite_type}vote-${comment_id}`
    let vote_icon = document.getElementById(id)
    let opposite_vote_icon = document.getElementById(opposite_id)

    if (!vote_icon.classList.contains('can_vote')) {
        return
    }
    
    vote_icon.classList.remove('can_vote')
    opposite_vote_icon.classList.add('can_vote')
    
    let like_count = document.getElementById(`likes-${comment_id}`)
    let current_likes = parseInt(like_count.innerText)
    let weight = type == 'up' ? 1 : -1
    like_count.innerText = current_likes + weight

    fetch(document.location, {
        method: "POST",
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        // body: form_data
        body: `vote_comment=1&comment_id=${comment_id}&vote_type=${type}`
    }).then((res) => {
        console.log(`${type}voted comment_id ${comment_id}`)
    })
}