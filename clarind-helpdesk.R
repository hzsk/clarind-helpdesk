t <- read.table('clarind-helpdesk.tsv', sep = '\t', header = TRUE,
                row.names = 1)
open <- t[1,]
merged <- c(t[2,])
reminder <- c(t[3,])
auto <- c(t[4,])
deleted <- c(t[5,])
closed <- c(t[6,])
newnew <- t[7,]
types = c("l", "l", "l", "l", "l", "l", "l")
colours = c("purple", "light green", "yellow", "orange",
            "pink", "green", "blue")
pchs = c("-", "-", "-", "-", "-", "-", "-")
legends = c("Open", "Merged (cumulative)", "Reminders",
           "Auto-closed", "Deleted", "Closed (cumulative)", "New")
ymax = 400
xmax = 75
pdf('clarind-helpdesk.pdf')
plot(cumsum(closed), main="CLARIN+D Helpdesk", ylab="Tickets",
     xlab = "Weeks",
     type=types[6], pch = pchs[6], col = colours[6],
     ylim = c(1, ymax))
points(cumsum(merged), type = types[2], pch = pchs[2], col = colours[2])
points(seq(1, xmax), newnew, type = types[7], col = colours[7], pch = pchs[7])
points(seq(1, xmax), open, type = types[1], col = colours[1], pch = pchs[1])
legend(1, ymax, legends, col = colours, pch = pchs)
summary(t)
