final class GDPRComplianceListener implements EventListenerInterface
{
public function handle(EventInterface $event): mixed
{
$result = $this->runCompliance($event);

if ($result->isBlocked()) {
$event->stopPropagation(); // ✅ no further listeners run
}

return $result;
}
}