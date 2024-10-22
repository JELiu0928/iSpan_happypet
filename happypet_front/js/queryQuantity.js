let user = localStorage.getItem("uid")
console.log('id',user)
if(!user){
    localStorage.removeItem("uid")
    localStorage.removeItem("cartQuantity")
}else{
    // console.log('執行')
    queryQuantity(user)
    // console.log('結束')
}
// 更新購物車數量(紅點圖標數量)
// function queryQuantity(){
    // fetch('http://localhost/happypet/happypet_back/public/api/productcart/1')
    function queryQuantity(user){
        fetch(`http://localhost/happypet/happypet_back/public/api/productcart/${user}`)
        .then(response=>response.text())
        .then(quantity=>{
            console.log('購物車quantity',quantity)
            
            if(!quantity || quantity == 0){
                // cartQuantity.style.display = 'none'
                $('.nav_icon .cart_quantity').addClass('d-none');
            }else{
                // cartQuantity.style.display = 'block'
                $('.nav_icon .cart_quantity').removeClass('d-none');
                $('.nav_icon .cart_quantity').text(quantity);
                localStorage.setItem("cartQuantity", quantity);
                console.log("購物車quantity localStorage",localStorage.getItem("cartQuantity"))
                // cartQuantity.innerText = quantity
            }
        })
    }
