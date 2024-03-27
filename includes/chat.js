async function get_messages() {
    let params = new URLSearchParams(location.search)
    params.delete("view")
    params.append("fetch", "messages")
    let resp = await fetch(`chat.php?${params.toString()}`)
    return await resp.json()
}

async function update_chat() {
    let chat_div1 = document.getElementById("chat_div1")
    let chat_div2 = document.getElementById("chat_div2")
    let messages = await get_messages()
    chat_div2.innerText = ""
    for (let i=messages.length-1; i>=0; i--) {
        let message = messages[i]
        let message_node = document.createElement('div')
        message_node.innerText = `${message.username}: ${message.content}`
        message_node.classList.add('message')

        let timestamp_node = document.createElement('div')
        timestamp_node.classList.add('timestamp')

        let date = new Date(message.timestamp)

        let options;
        
        if (date.getTime() > Date.now() - 1000 * 60 * 60 * 24) {
            options = {
                hour: 'numeric',
                minute: 'numeric'
            }
        } else {
            options = {
                dateStyle: 'short',
                // month: 'numeric',
                // day: 'numeric',
            }
        }

        let datefmt = new Intl.DateTimeFormat('en-US', options)

        timestamp_node.innerText = datefmt.format(date)
        message_node.prepend(timestamp_node)
        chat_div2.appendChild(message_node)
    }
    chat_div1.innerHTML = chat_div2.innerHTML
}

async function send_message(message_content) {
    let params = new URLSearchParams(location.search)
    params.delete("view")
    return fetch(
        `chat.php?${params.toString()}`,
        {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `send_message&message_content=${encodeURIComponent(message_content)}`
        }
    )
}