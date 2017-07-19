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
responsetimes <- t$FirstResponseInMin / 60 / 24
closetimesfull <- t$SolutionInMin / 60 / 24
closetimes <- remove_outliers(t$SolutionInMin / 60 / 24)
responseavg <- mean(t$firstResponseInMin, na.rm=TRUE)
responsemedian <- median(t$firstResponseInMin, na.rm=TRUE)
closeavg <- mean(t$SolutionInMin, na.rm=TRUE)
closemedian <- median(t$SolutionInMin, na.rm=TRUE)

pdf('clarind-helpdesk-times.pdf')

boxplot(closetimes, responsetimes,
        main="CLARIN+D Helpdesk ticket handling times in days",
        ylab="Days", names=c("Closing", "First response"),
        col=c("red", "blue"))
summary(closetimesfull)
summary(responsetimes)

