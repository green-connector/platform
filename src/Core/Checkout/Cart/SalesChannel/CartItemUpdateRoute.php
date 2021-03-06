<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\SalesChannel;

use OpenApi\Annotations as OA;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartCalculator;
use Shopware\Core\Checkout\Cart\CartPersisterInterface;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class CartItemUpdateRoute extends AbstractCartItemUpdateRoute
{
    /**
     * @var CartPersisterInterface
     */
    private $cartPersister;

    /**
     * @var CartCalculator
     */
    private $cartCalculator;

    /**
     * @var LineItemFactoryRegistry
     */
    private $lineItemFactory;

    public function __construct(CartPersisterInterface $cartPersister, CartCalculator $cartCalculator, LineItemFactoryRegistry $lineItemFactory)
    {
        $this->cartPersister = $cartPersister;
        $this->cartCalculator = $cartCalculator;
        $this->lineItemFactory = $lineItemFactory;
    }

    public function getDecorated(): AbstractCartItemUpdateRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @Since("6.3.0.0")
     * @OA\Patch(
     *      path="/checkout/cart/line-item",
     *      summary="Update line item entries",
     *      operationId="updateLineItem",
     *      tags={"Store API", "Cart"},
     *      @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/CartItems")),
     *      @OA\Response(
     *          response="200",
     *          description="Cart",
     *          @OA\JsonContent(ref="#/components/schemas/Cart")
     *     )
     * )
     * @Route("/store-api/v{version}/checkout/cart/line-item", name="store-api.checkout.cart.update-lineitem", methods={"PATCH"})
     */
    public function change(Request $request, Cart $cart, SalesChannelContext $context): CartResponse
    {
        foreach ($request->request->get('items', []) as $item) {
            $this->lineItemFactory->update($cart, $item, $context);
        }

        $cart->markModified();

        $cart = $this->cartCalculator->calculate($cart, $context);

        $this->cartPersister->save($cart, $context);

        return new CartResponse($cart);
    }
}
