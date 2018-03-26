t <- read.table('ticket-times.tsv', sep = '\t', header = TRUE,
                row.names = 1)
remove_outliers <- function(x, na.rm = TRUE, ...) {
  qnt <- quantile(x, probs=c(.1, .9), na.rm = na.rm, ...)
  H <- 1.5 * IQR(x, na.rm = na.rm)
  y <- x
  y[x < (qnt[1] - H)] <- NA
  y[x > (qnt[2] + H)] <- NA
  y
}
responsetimes <- t$Erstantwortzeit.In.Minuten / 60 / 8
closetimesfull <- t$Lösungszeit.In.Minuten / 60 / 8
closetimes <- remove_outliers(t$Lösungszeit.In.Minuten / 60 / 8)
responseavg <- mean(t$Erstantwortzeit.In.Minuten, na.rm=TRUE)
responsemedian <- median(t$Erstantwortzeit.In.Minuten, na.rm=TRUE)
closeavg <- mean(t$Lösungszeit.In.Minuten, na.rm=TRUE)
closemedian <- median(t$Lösungszeit.In.Minuten, na.rm=TRUE)

pdf('clarind-helpdesk-times.pdf')

summary(closetimesfull)
summary(responsetimes)
boxplot(closetimes, responsetimes,
        main="CLARIN+D Helpdesk ticket handling times in days",
        ylab="Days", names=c("close", "First response"),
        col=c("red", "blue"))

