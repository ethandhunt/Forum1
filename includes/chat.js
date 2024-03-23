async function get_messages() {
    let params = new URLSearchParams(location.search)
    params.delete("view")
    params.append("fetch", "messages")
    let resp = await fetch(`chat.php?${params.toString()}`)
    return await resp.json()
}

async function update_chat() {
    let chat_div = document.getElementById("chat_div")
    let messages = await get_messages()
    chat_div.innerText = ""
    for (let i=messages.length-1; i>=0; i--) {
        let message = messages[i]
        chat_div.innerText = chat_div.innerText.concat(`${message.username}: ${message.content}`)
        chat_div.innerHTML = chat_div.innerHTML.concat('<br>')
    }
}

async function send_message(message_content) {
    let params = new URLSearchParams(location.search)
    params.delete("view")
    return fetch(
        `chat.php?${params.toString()}`,
        {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `send_message&message_content=${message_content}`
        }
    )
}