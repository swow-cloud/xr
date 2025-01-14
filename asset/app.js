let filter = {
        topic: "",
        emote: ""
    },
    filterOperator = {
        topic: "=",
        emote: "*="
    },
    currentStatus = "resume",
    queuedMessageCount = 0,
    windowActions = {
        pushStatus: function (status) {
            setStatus(status);
            if (status === "resume") {
                this.resume();
            }
        },
        resume: function () {
            document.querySelectorAll(".message--while-pause").forEach(function (el) {
                el.classList.remove("message--while-pause");
            })
            this.resetQueue();
        },
        resetQueue: function () {
            queuedMessageCount = 0;
            document.getElementById("queue-count").textContent = "";
        },
        clear: function () {
            document.querySelector("main").innerHTML = "";
            filter = {
                topic: "",
                emote: ""
            };
            this.resetQueue();
        }
    },
    buttonActions = function (action) {
        if (action === currentStatus || document.body.classList.contains("body--splash")) {
            return;
        }
        if (action === "clear") {
            windowActions.clear();
        } else {
            windowActions.pushStatus(action);
        }
        pushMessage({
            message: "<b>" + action.toUpperCase() + "</b> " + document.title
        }, true);
    },
    keysToAction = {
        KeyS: "stop",
        KeyR: "resume",
        KeyP: "pause",
        KeyC: "clear"
    },
    setStatus = function (status) {
        document.querySelectorAll(".button--active").forEach(function (el) {
            el.classList.remove("button--active")
        });
        document.querySelectorAll("[data-action=" + status + "]").forEach(function (el) {
            el.classList.add("button--active");
        });
        if (status === "clear") {
            setStatus(currentStatus);
            return;
        }
        currentStatus = status;

        return currentStatus;
    },
    templates = {
        message: document.querySelector("#message")
    };
copyToClipboard = function(text) {
    try {
        navigator.clipboard.writeText(text);
    } catch (ex) {
        if (window.clipboardData && window.clipboardData.setData) {
            return window.clipboardData.setData("Text", text);
        } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
            let textarea = document.createElement("textarea");
            textarea.textContent = text;
            textarea.style.position = "fixed";
            document.body.appendChild(textarea);
            textarea.select();
            try {
                return document.execCommand("copy");
            } catch (ex) {
                console.warn("Copy to clipboard failed.", ex);
                return prompt("Copy to clipboard: Ctrl+C, Enter", text);
            } finally {
                document.body.removeChild(textarea);
            }
        }
    }
}
pushMessage = function (data, isStatus = false) {
    let el = templates.message.content.cloneNode(true);
    el.querySelector(".time").textContent = (new Date()).toTimeString().split(" ")[0];
    el.querySelector(".topic").textContent = data.topic;
    el.querySelector(".emote").textContent = data.emote;
    el.querySelector(".body-raw").innerHTML = data.message;
    let bodyContextDisplay = el.querySelector(".body-context-display");
    bodyContextDisplay.textContent = data.file_display_short;
    if (data.file_display_short) {
        bodyContextDisplay.textContent = "・" + data.file_display_short;
        bodyContextDisplay.setAttribute("title", data.file_display || "");
    }
    if (document.body.classList.contains("body--splash")) {
        document.body.classList.remove("body--splash", "body--splash-in");
    }
    document.querySelector("main").prepend(el);
    el = document.querySelector(".message:first-child");
    el.classList.add("message--loading");
    el.dataset.emote = data.emote ? data.emote : "";
    el.dataset.topic = data.topic ? data.topic : "";
    if (! isStatus && currentStatus === "pause") {
        queuedMessageCount++;
        el.classList.add("message--while-pause");
        document.getElementById("queue-count").textContent = queuedMessageCount;
    }
    setTimeout(function () {
        el.classList.remove("message--loading");
    }, 250);
}
setStatus(currentStatus);
for (key in keysToAction) {
    document.querySelectorAll("[data-action=" + keysToAction[key] + "]").forEach(function (el) {
        el.addEventListener("click", function () {
            buttonActions(this.dataset.action);
        });
    })
}
document.addEventListener("keyup", function (event) {
    if (event.target.classList.contains("no-keys")) {
        return;
    }
    if (event.code in keysToAction && !(event.metaKey || event.ctrlKey || event.altKey)) {
        buttonActions(keysToAction[event.code]);
    }
})
document.querySelector(".header-title").addEventListener("paste", event => {
    event.preventDefault();
    let text = (event.originalEvent || event).clipboardData.getData("text/plain");
    document.execCommand("insertHTML", false, text);
});
document.querySelector(".header-title").addEventListener("input", event => {
    document.title = event.target.textContent;
});
document.addEventListener("click", event => {
    var el = event.target;
    var messageEl = el.closest(".message");
    switch (el.dataset.action) {
        case "remove": messageEl.classList.add("message--removing");
            setTimeout(function () {
                messageEl.remove();
            }, 250);
            break;
        case "copy": copyToClipboard(messageEl.querySelector(".body-raw").textContent + "\n" + messageEl.querySelector(".body-context").textContent.replace(/[\n\r]+|[\s]{2,}/g, ''));
            break;
        case "export":
            html2canvas(messageEl.querySelector(".body"), {scale: window.devicePixelRatio * 2}).then(function (canvas) {
                let dataUrl = canvas.toDataURL("image/jpg");
                let link = document.createElement("a");
                link.download = document.title + "-" + messageEl.querySelector(".time").textContent + ".jpg";
                link.href = dataUrl;
                link.click();
            });
            break;
    }
    if (el.classList.contains("body-context-display")) {
        copyToClipboard(el.getAttribute("title"));
    }
    if (el.classList.contains("filter-button")) {
        var filterQuery = "",
            messageEl = messageEl,
            subject = el.classList.contains("topic") ? "topic" : "emote";
        if (messageEl && filter[subject] === messageEl.dataset[subject]) {
            return;
        }
        filter[subject] = messageEl ? messageEl.dataset[subject] : "";
        for (filterSubject in filter) {
            if (filter[filterSubject] === "") {
                continue;
            }
            filterQuery += "[data-" + filterSubject + filterOperator[filterSubject] + filter[filterSubject] + "]";
        }
        document.getElementById("filtering").innerHTML = filterQuery === ""
            ? ""
            : ".message:not(" + filterQuery + ") { display: none; }";
        document.querySelector(".header-filter ." + subject).textContent = messageEl
            ? filter[subject]
            : "";
    }
});
document.body.classList.add("body--splash-in");
es = new EventSource("dump");
es.addEventListener("message", function (event) {
    if (currentStatus === "stop") {
        return;
    }
    pushMessage(JSON.parse(event.data));
});
