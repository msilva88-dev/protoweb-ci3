function consLog(log, data0 = null, data1 = null, data2 = null) {
    switch (log) {
    case "chat_delmsg_error":
        console.error("Error deleting message.");
        break;
    case "chat_delmsg_fail":
        console.error("Failed to delete message.");
        break;
    case "chat_rendmsg_sidmiss":
        console.error("Message Sender ID is missing.");
        break;
    }
}
