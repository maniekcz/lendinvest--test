<?php

declare(strict_types=1);

namespace Lendinvest\Loan\Application\Command\Handler;

use Lendinvest\Loan\Application\Command\InvestMoney;
use Lendinvest\Loan\Domain\Exception\InvestorCannotInvest;
use Lendinvest\Loan\Domain\Exception\TrancheIsNotDefined;
use Lendinvest\Loan\Domain\Investment\Investment;
use Lendinvest\Loan\Domain\Investor\Investors;
use Lendinvest\Loan\Domain\Loans;

class InvestMoneyHandler
{
    /**
     * @var Loans
     */
    private $loans;

    /**
     * @var Investors
     */
    private $investors;

    public function __construct(Loans $loans, Investors $investors)
    {
        $this->loans = $loans;
        $this->investors = $investors;
    }

    /**
     * @param InvestMoney $command
     * @throws InvestorCannotInvest
     * @throws TrancheIsNotDefined
     */
    public function __invoke(InvestMoney $command)
    {
        $loan = $this->loans->get($command->loanId());
        $investor = $this->investors->get($command->investorId());

        $investment = Investment::create(
            $command->investmentId(),
            $investor,
            $loan->getTranche($command->trancheId()),
            $command->amount(),
            $command->created()
        );

        $loan->invest($investment);
        $investor->invest($command->amount());

        $this->investors->save($investor);
        $this->loans->save($loan);
    }
}
